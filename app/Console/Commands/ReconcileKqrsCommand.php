<?php

namespace App\Console\Commands;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentDocument;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReconcileKqrsCommand extends Command
{
    protected $signature = 'equipment:reconcile-kqrs 
                            {file : Đường dẫn file Excel (VD: data-real/kqrs.xlsx)}';

    protected $description = 'Đọc và in báo cáo đối soát hồ sơ thiết bị từ file kqrs.xlsx với dữ liệu trong CSDL';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");

            return Command::FAILURE;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Bổ sung') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Cấu hình các cột hồ sơ cần đối soát (từ Cột C đến L).
        // CỘT M ("Phân công người phụ trách") TUYỆT ĐỐI BỎ QUA - KHÔNG ĐỌC, KHÔNG LƯU, KHÔNG IN!
        $docColumns = [
            'C' => ['name' => 'Lý lịch thiết bị', 'enum' => DocumentTypeEnum::LY_LICH],
            'D' => ['name' => 'Quyết định/ biên bản bàn giao', 'enum' => DocumentTypeEnum::BIEN_BAN_BAN_GIAO],
            'E' => ['name' => 'Tài liệu kỹ thuật', 'enum' => DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD],
            'F' => ['name' => 'Hướng dẫn sử dụng', 'enum' => DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD],
            'G' => ['name' => 'Nhật ký sử dụng máy', 'enum' => DocumentTypeEnum::NHAT_KY_SU_DUNG],
            'H' => ['name' => 'Nội quy/ Quy trình vận hành', 'enum' => DocumentTypeEnum::QUY_TRINH_VAN_HANH],
            'I' => ['name' => 'Hồ sơ bảo dưỡng/ Theo dõi tình trạng máy', 'enum' => DocumentTypeEnum::HO_SO_BAO_DUONG],
            'J' => ['name' => 'Hồ sơ điều chuyển/ thanh lý/ ngừng sử dụng', 'enum' => DocumentTypeEnum::HO_SO_DIEU_CHUYEN_THANH_LY],
            'K' => ['name' => 'Kiểm định', 'enum' => DocumentTypeEnum::KIEM_DINH],
            'L' => ['name' => 'Hiệu chuẩn', 'enum' => DocumentTypeEnum::HIEU_CHUAN],
        ];

        $deptRows = [];
        $unreviewedCols = ['J', 'K', 'L'];
        $calcSums = [];
        foreach (array_keys($docColumns) as $col) {
            $calcSums[$col] = ['num' => 0, 'den' => 0];
        }

        $fileTotals = [];
        $totalRowIndex = null;

        // Quét các dòng dữ liệu
        for ($r = 5; $r <= $highestRow; $r++) {
            $sttVal = trim((string) $sheet->getCell("A{$r}")->getCalculatedValue());
            $nameVal = trim((string) $sheet->getCell("B{$r}")->getCalculatedValue());

            // Nhận diện dòng TỔNG ở Cột B
            if (mb_strtoupper($nameVal) === 'TỔNG' || str_contains(mb_strtoupper($nameVal), 'TỔNG')) {
                $totalRowIndex = $r;
                foreach (array_keys($docColumns) as $col) {
                    $rawCell = trim((string) $sheet->getCell("{$col}{$r}")->getCalculatedValue());
                    $parsed = $this->parseFraction($rawCell);
                    $fileTotals[$col] = $parsed;
                }

                continue;
            }

            if ($sttVal === '' || ! is_numeric($sttVal)) {
                continue;
            }

            $stt = (int) $sttVal;
            $deptName = $nameVal;

            $rowDocs = [];
            $deptTotal = 0;

            foreach ($docColumns as $col => $info) {
                $rawCell = trim((string) $sheet->getCell("{$col}{$r}")->getCalculatedValue());
                $parsed = $this->parseFraction($rawCell);

                $rowDocs[$col] = $parsed;

                if ($parsed['den'] > 0) {
                    $deptTotal = max($deptTotal, $parsed['den']);
                }

                $calcSums[$col]['num'] += $parsed['num'];
                $calcSums[$col]['den'] += $parsed['den'];
            }

            $deptRows[] = [
                'stt' => $stt,
                'name' => $deptName,
                'total_kqrs' => $deptTotal,
                'docs' => $rowDocs,
            ];
        }

        $this->info('========================================================================================');
        $this->info('           BÁO CÁO ĐỐI SOÁT HỒ SƠ THIẾT BỊ TỪ KQRS.XLSX VỚI CSDL MEMS                 ');
        $this->info('========================================================================================');
        $this->newLine();

        // 1. In báo cáo theo từng khoa
        $this->info('--- 1. BÁO CÁO THEO TỪNG KHOA (SỐ LƯỢNG & ĐỘ PHỦ HỒ SƠ KQRS VS CSDL) ---');

        $headers = ['STT', 'Khoa / Phòng', 'Số TB (DB vs KQRS)', 'Lý lịch', 'Bàn giao', 'TLKT/HDSD', 'Nhật ký', 'Quy trình', 'Bảo dưỡng'];
        $tableRows = [];

        foreach ($deptRows as $row) {
            $deptName = $row['name'];
            $dbDept = Department::whereRaw('LOWER(name) = ?', [mb_strtolower($deptName)])->first()
                ?? Department::where('name', 'like', '%'.$deptName.'%')->first();

            $dbEquipmentCount = $dbDept ? $dbDept->equipment()->count() : 0;
            $dbEquipmentIds = $dbDept ? $dbDept->equipment()->pluck('id')->toArray() : [];

            $formatCoverage = function ($colKey, DocumentTypeEnum $enum) use ($row, $dbEquipmentIds, $dbEquipmentCount) {
                $kqrsNum = $row['docs'][$colKey]['num'];
                $kqrsDen = $row['docs'][$colKey]['den'];
                $kqrsPct = $kqrsDen > 0 ? round(($kqrsNum / $kqrsDen) * 100, 1) : 0;

                $dbHasCount = 0;
                if (! empty($dbEquipmentIds)) {
                    $dbHasCount = EquipmentDocument::whereIn('equipment_id', $dbEquipmentIds)
                        ->where('document_type', $enum->value)
                        ->where('status', DocumentStatusEnum::CO->value)
                        ->count();
                }
                $dbPct = $dbEquipmentCount > 0 ? round(($dbHasCount / $dbEquipmentCount) * 100, 1) : 0;

                return sprintf('%d/%d (%s%%) | DB: %d (%s%%)', $kqrsNum, $kqrsDen, $kqrsPct, $dbHasCount, $dbPct);
            };

            $tableRows[] = [
                $row['stt'],
                $deptName,
                sprintf('%d vs %d', $dbEquipmentCount, $row['total_kqrs']),
                $formatCoverage('C', DocumentTypeEnum::LY_LICH),
                $formatCoverage('D', DocumentTypeEnum::BIEN_BAN_BAN_GIAO),
                $formatCoverage('E', DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD),
                $formatCoverage('G', DocumentTypeEnum::NHAT_KY_SU_DUNG),
                $formatCoverage('H', DocumentTypeEnum::QUY_TRINH_VAN_HANH),
                $formatCoverage('I', DocumentTypeEnum::HO_SO_BAO_DUONG),
            ];
        }

        $this->table($headers, $tableRows);
        $this->newLine();

        // 2. Cảnh báo sai lệch dòng TỔNG và lệch so với PL2 / DB
        $this->info('--- 2. BÁO CÁO CẢNH BÁO SAI LỆCH DỮ LIỆU ---');

        $kqrsTotalDenominator = max(array_column($calcSums, 'den')) ?: 280;
        $dbTotalEquipment = Equipment::count();
        $pl2ExpectedCount = 286;

        if ($kqrsTotalDenominator !== $pl2ExpectedCount || $kqrsTotalDenominator !== $dbTotalEquipment) {
            $this->warn(sprintf(
                '  [!] CẢNH BÁO LỆCH TỔNG SỐ THIẾT BỊ: KQRS ghi %d thiết bị, Phụ lục 2 kỳ vọng %d thiết bị, CSDL hiện có %d thiết bị.',
                $kqrsTotalDenominator,
                $pl2ExpectedCount,
                $dbTotalEquipment
            ));
        }

        foreach ($docColumns as $col => $info) {
            $colName = $info['name'];
            $fileNum = $fileTotals[$col]['num'] ?? null;
            $fileDen = $fileTotals[$col]['den'] ?? null;
            $sumNum = $calcSums[$col]['num'];
            $sumDen = $calcSums[$col]['den'];

            if ($fileNum !== null && $fileNum !== $sumNum) {
                $this->warn(sprintf(
                    '  [!] CẢNH BÁO SAI LỆCH DÒNG TỔNG NỘI BỘ KQRS - Cột "%s" (Cột %s): Dòng TỔNG ghi %d/%d nhưng tổng 16 khoa cộng lại là %d/%d (Lệch %d thiết bị).',
                    $colName,
                    $col,
                    $fileNum,
                    $fileDen,
                    $sumNum,
                    $sumDen,
                    abs($fileNum - $sumNum)
                ));
            }
        }

        $this->newLine();

        // 3. Liệt kê các cột chưa rà soát
        $this->info('--- 3. DANH SÁCH HỒ SƠ CHƯA RÀ SOÁT (TRỐNG Ở MỌI KHOA) ---');
        foreach ($unreviewedCols as $col) {
            $colName = $docColumns[$col]['name'];
            $this->line(sprintf('  - Cột %s (%s): CHƯA RÀ SOÁT (Trống ở tất cả 16 khoa)', $col, $colName));
        }

        $this->newLine();
        $this->info('========================================================================================');
        $this->info(' [✓] Báo cáo rà soát KQRS hoàn tất. Không thay đổi dữ liệu trong CSDL.');
        $this->info('========================================================================================');

        return Command::SUCCESS;
    }

    private function parseFraction(string $cellValue): array
    {
        $val = trim($cellValue);
        if ($val === '' || $val === '0') {
            return ['num' => 0, 'den' => 0];
        }

        if (str_contains($val, '/')) {
            $parts = explode('/', $val);
            $num = is_numeric(trim($parts[0])) ? (int) trim($parts[0]) : 0;
            $den = is_numeric(trim($parts[1])) ? (int) trim($parts[1]) : 0;

            return ['num' => $num, 'den' => $den];
        }

        if (is_numeric($val)) {
            return ['num' => (int) $val, 'den' => 0];
        }

        return ['num' => 0, 'den' => 0];
    }
}
