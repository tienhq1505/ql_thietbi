<?php

namespace App\Console\Commands;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\ImportBatch;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPl2EquipmentCommand extends Command
{
    protected $signature = 'equipment:import-pl2 
                            {file : Đường dẫn file Excel (VD: data-real/PL2_TBYT.xlsx)} 
                            {--commit : Ghi trực tiếp vào CSDL (Mặc định là DRY-RUN chỉ tạo file review CSV)}';

    protected $description = 'Import dữ liệu thiết bị từ Phụ lục 2 (PL2_TBYT.xlsx) với tính năng Dry-Run, phân tích cột E, kiểm tra hồ sơ và Idempotence';

    private array $countryAlias = [
        'trung quốc' => 'Trung Quốc', 'china' => 'Trung Quốc', 'trung quoc' => 'Trung Quốc',
        'nhật bản' => 'Nhật Bản', 'nhật' => 'Nhật Bản', 'japan' => 'Nhật Bản', 'nhat ban' => 'Nhật Bản',
        'hàn quốc' => 'Hàn Quốc', 'hàn' => 'Hàn Quốc', 'korea' => 'Hàn Quốc', 'han quoc' => 'Hàn Quốc',
        'đức' => 'Đức', 'germany' => 'Đức', 'duc' => 'Đức',
        'mỹ' => 'Mỹ', 'usa' => 'Mỹ', 'my' => 'Mỹ',
        'ý' => 'Ý', 'italy' => 'Ý', 'italia' => 'Ý',
        'malaysia' => 'Malaysia',
        'việt nam' => 'Việt Nam', 'vietnam' => 'Việt Nam', 'viet nam' => 'Việt Nam',
        'thụy sĩ' => 'Thụy Sĩ', 'thụy sỹ' => 'Thụy Sĩ', 'switzerland' => 'Thụy Sĩ',
        'pháp' => 'Pháp', 'france' => 'Pháp', 'phap' => 'Pháp',
        'anh' => 'Anh', 'uk' => 'Anh', 'england' => 'Anh',
        'đài loan' => 'Đài Loan', 'taiwan' => 'Đài Loan',
        'ấn độ' => 'Ấn Độ', 'india' => 'Ấn Độ',
        'thái lan' => 'Thái Lan', 'thailand' => 'Thái Lan',
        'tây ban nha' => 'Tây Ban Nha', 'spain' => 'Tây Ban Nha',
        'thụy điển' => 'Thụy Điển', 'sweden' => 'Thụy Điển',
        'hà lan' => 'Hà Lan', 'netherlands' => 'Hà Lan',
        'đan mạch' => 'Đan Mạch', 'denmark' => 'Đan Mạch',
        'bỉ' => 'Bỉ', 'belgium' => 'Bỉ',
        'singapore' => 'Singapore',
        'israel' => 'Israel',
        'úc' => 'Úc', 'australia' => 'Úc',
        'phần lan' => 'Phần Lan', 'finland' => 'Phần Lan',
        'thổ nhĩ kỳ' => 'Thổ Nhĩ Kỳ', 'turkey' => 'Thổ Nhĩ Kỳ',
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");

            return Command::FAILURE;
        }

        $isCommit = (bool) $this->option('commit');
        $this->info($isCommit ? '>>> Chế độ: COMMIT (Sẽ ghi vào CSDL)' : '>>> Chế độ: DRY-RUN (Chỉ kiểm tra & tạo file review CSV)');

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $department = Department::where('code', 'CHUA-PHAN-KHOA')->first()
            ?? Department::create(['code' => 'CHUA-PHAN-KHOA', 'name' => 'Chưa phân khoa', 'short_name' => 'Chưa phân khoa']);

        // Master Data mặc định
        $defaultCat = EquipmentCategory::firstOrCreate(
            ['code' => 'CAT-TH'],
            ['name' => 'Thiết bị Y tế tổng hợp', 'description' => 'Danh mục mặc định']
        );

        $defaultMfg = Manufacturer::firstOrCreate(
            ['code' => 'MFG-UNKNOWN'],
            ['name' => 'Chưa xác định', 'country' => 'Chưa rõ']
        );

        $defaultModel = EquipmentModel::firstOrCreate(
            ['code' => 'MOD-UNKNOWN'],
            [
                'name' => 'Model chưa xác định',
                'category_id' => $defaultCat->id,
                'manufacturer_id' => $defaultMfg->id,
                'specs' => 'Model mặc định cho dữ liệu chưa phân loại',
            ]
        );

        $parsedRows = [];
        $totalUnits = 0;
        $qtyGt1Count = 0;
        $lowConfCount = 0;
        $domesticImportNoCount = 0;

        $docStats = [
            'hop_dong_hoa_don' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'bien_ban_ban_giao' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'ho_so_luu_hanh' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'co' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'cq' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'to_khai_hai_quan' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'tai_lieu_ky_thuat_hdsd' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
            'ho_so_cong_bo_dieu_kien_mua_ban' => ['co' => 0, 'khong' => 0, 'chua_ra_soat' => 0, 'khong_ap_dung' => 0],
        ];

        // Quét các ô gộp ở Cột F
        for ($r = 6; $r <= $highestRow; $r++) {
            $sttVal = trim((string) $sheet->getCell("A{$r}")->getCalculatedValue());
            if ($sttVal === '' || ! is_numeric($sttVal)) {
                continue; // Bỏ qua hàng tiêu đề / ghi chú không có STT
            }

            $stt = (int) $sttVal;
            $name = trim((string) $sheet->getCell("B{$r}")->getCalculatedValue());
            $unit = trim((string) $sheet->getCell("C{$r}")->getCalculatedValue());
            $qtyRaw = $sheet->getCell("D{$r}")->getCalculatedValue();
            $qty = is_numeric($qtyRaw) && (int) $qtyRaw > 0 ? (int) $qtyRaw : 1;

            if ($qty > 1) {
                $qtyGt1Count++;
            }
            $totalUnits += $qty;

            $colE = trim((string) $sheet->getCell("E{$r}")->getCalculatedValue());
            $parsedE = $this->parseColumnE($colE);

            if ($parsedE['confidence'] === 'thap') {
                $lowConfCount++;
            }

            // Đọc cột F với xử lý ô gộp (fill-down)
            $cellF = $sheet->getCell("F{$r}");
            $valF = trim((string) $cellF->getCalculatedValue());
            if ($cellF->isInMergeRange()) {
                $range = $cellF->getMergeRange();
                $topCell = explode(':', $range)[0];
                $valF = trim((string) $sheet->getCell($topCell)->getCalculatedValue());
            }

            $isMayDat = str_contains(mb_strtoupper($valF), 'MÁY ĐẶT');
            $ownershipType = $isMayDat ? OwnershipTypeEnum::MAY_DAT : OwnershipTypeEnum::MUA_SAM;

            // Đọc 8 trạng thái hồ sơ
            $rawDocs = [
                'hop_dong_hoa_don' => $isMayDat ? 'khong_ap_dung' : $valF,
                'bien_ban_ban_giao' => trim((string) $sheet->getCell("G{$r}")->getCalculatedValue()),
                'ho_so_luu_hanh' => trim((string) $sheet->getCell("H{$r}")->getCalculatedValue()),
                'co' => trim((string) $sheet->getCell("I{$r}")->getCalculatedValue()),
                'cq' => trim((string) $sheet->getCell("J{$r}")->getCalculatedValue()),
                'to_khai_hai_quan' => trim((string) $sheet->getCell("K{$r}")->getCalculatedValue()),
                'tai_lieu_ky_thuat_hdsd' => trim((string) $sheet->getCell("L{$r}")->getCalculatedValue()),
                'ho_so_cong_bo_dieu_kien_mua_ban' => trim((string) $sheet->getCell("M{$r}")->getCalculatedValue()),
            ];

            $docStatuses = [];
            foreach ($rawDocs as $key => $rawVal) {
                $statusEnum = ($key === 'hop_dong_hoa_don' && $rawVal === 'khong_ap_dung')
                    ? DocumentStatusEnum::KHONG_AP_DUNG
                    : $this->parseDocStatus($rawVal);

                $docStatuses[$key] = $statusEnum;
                $docStats[$key][$statusEnum->value]++;
            }

            // Thống kê thiết bị nội địa (Việt Nam) có hồ sơ "Không"
            if ($parsedE['country'] === 'Việt Nam') {
                $hasNoInImportDocs = (
                    $docStatuses['ho_so_luu_hanh'] === DocumentStatusEnum::KHONG ||
                    $docStatuses['co'] === DocumentStatusEnum::KHONG ||
                    $docStatuses['cq'] === DocumentStatusEnum::KHONG ||
                    $docStatuses['to_khai_hai_quan'] === DocumentStatusEnum::KHONG
                );
                if ($hasNoInImportDocs) {
                    $domesticImportNoCount++;
                }
            }

            $parsedRows[] = [
                'stt' => $stt,
                'name' => $name,
                'unit' => $unit,
                'qty' => $qty,
                'col_e' => $colE,
                'country' => $parsedE['country'],
                'year' => $parsedE['year'],
                'model' => $parsedE['model'],
                'mfg' => $parsedE['mfg'],
                'confidence' => $parsedE['confidence'],
                'ownership_type' => $ownershipType,
                'doc_statuses' => $docStatuses,
            ];
        }

        // Tạo file review CSV tại storage/app/import-review/pl2_review.csv
        $reviewDir = storage_path('app/import-review');
        if (! file_exists($reviewDir)) {
            mkdir($reviewDir, 0755, true);
        }

        $reviewCsvPath = $reviewDir.'/pl2_review.csv';
        $csvHandle = fopen($reviewCsvPath, 'w');
        fwrite($csvHandle, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($csvHandle, [
            'STT', 'Tên thiết bị', 'ĐVT', 'Số lượng', 'Chuỗi gốc (Cột E)',
            'Model nhận diện', 'Hãng nhận diện', 'Nước SX', 'Năm SX',
            'Độ tin cậy', 'Loại sở hữu', 'Hợp đồng/Hóa đơn', 'Biên bản bàn giao',
            'Hồ sơ lưu hành', 'CO', 'CQ', 'Tờ khai hải quan', 'Tài liệu KT/HDSD',
            'Công bố đủ ĐK mua bán', 'Mã TB dự kiến', 'Ghi chú review',
        ]);

        foreach ($parsedRows as $row) {
            $expectedCodes = [];
            for ($i = 1; $i <= $row['qty']; $i++) {
                $expectedCodes[] = sprintf('TB-%03d-%02d', $row['stt'], $i);
            }

            $reviewNote = '';
            if ($row['confidence'] === 'thap') {
                $reviewNote .= '[Độ tin cậy thấp: cần sửa tay Model/Hãng] ';
            }
            if ($row['ownership_type'] === OwnershipTypeEnum::MAY_DAT) {
                $reviewNote .= '[Máy đặt từ nhà cung cấp] ';
            }

            fputcsv($csvHandle, [
                $row['stt'],
                $row['name'],
                $row['unit'],
                $row['qty'],
                $row['col_e'],
                $row['model'] ?? '',
                $row['mfg'] ?? '',
                $row['country'] ?? '',
                $row['year'] ?? '',
                $row['confidence'],
                $row['ownership_type']->label(),
                $row['doc_statuses']['hop_dong_hoa_don']->label(),
                $row['doc_statuses']['bien_ban_ban_giao']->label(),
                $row['doc_statuses']['ho_so_luu_hanh']->label(),
                $row['doc_statuses']['co']->label(),
                $row['doc_statuses']['cq']->label(),
                $row['doc_statuses']['to_khai_hai_quan']->label(),
                $row['doc_statuses']['tai_lieu_ky_thuat_hdsd']->label(),
                $row['doc_statuses']['ho_so_cong_bo_dieu_kien_mua_ban']->label(),
                implode(', ', $expectedCodes),
                trim($reviewNote),
            ]);
        }
        fclose($csvHandle);

        // THỰC HIỆN GHI CSDL KHI CÓ OPTION --commit
        if ($isCommit) {
            $adminUser = auth()->user() ?? User::first() ?? User::create([
                'name' => 'System Importer',
                'email' => 'system-importer@mems.local',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);
            $batch = ImportBatch::create([
                'file_name' => basename($filePath),
                'imported_by' => $adminUser->id,
                'total_rows' => count($parsedRows),
                'success_rows' => count($parsedRows),
                'failed_rows' => 0,
                'status' => 'completed',
            ]);

            foreach ($parsedRows as $row) {
                // Xử lý Master Data (Ngoại lệ có chủ ý cho Command Import PL2)
                $modelId = $defaultModel->id;
                $catId = $defaultCat->id;
                $mfgId = $defaultMfg->id;

                // CHỈ tự động tạo Model/Hãng mới khi có độ tin cậy CAO. Dòng độ tin cậy THẤP gán mặc định & đưa vào review CSV
                if ($row['confidence'] === 'cao') {
                    $mfgName = ! empty($row['mfg']) ? $row['mfg'] : 'Chưa xác định';
                    $mfgCode = 'MFG-'.Str::slug($mfgName);
                    if (empty($mfgCode) || $mfgCode === 'MFG-') {
                        $mfgCode = 'MFG-PL2-'.$row['stt'];
                    }

                    $mfgObj = Manufacturer::firstOrCreate(
                        ['code' => mb_strtoupper($mfgCode)],
                        ['name' => $mfgName, 'country' => $row['country'] ?? 'Chưa rõ']
                    );
                    $mfgId = $mfgObj->id;

                    $modelName = ! empty($row['model']) ? $row['model'] : $row['name'];
                    $modelCode = 'MOD-'.Str::slug($modelName);
                    if (empty($modelCode) || $modelCode === 'MOD-') {
                        $modelCode = 'MOD-PL2-'.$row['stt'];
                    }

                    $modelObj = EquipmentModel::firstOrCreate(
                        ['code' => mb_strtoupper($modelCode)],
                        [
                            'name' => $modelName,
                            'category_id' => $catId,
                            'manufacturer_id' => $mfgId,
                            'specs' => 'Tự động tạo từ import PL2: '.$row['col_e'],
                        ]
                    );
                    $modelId = $modelObj->id;
                }

                // Sinh N bản ghi equipment cho dòng có số lượng N (Idempotent qua code & source_ref)
                for ($i = 1; $i <= $row['qty']; $i++) {
                    $eqCode = sprintf('TB-%03d-%02d', $row['stt'], $i);
                    $sourceRef = "PL2#{$row['stt']}";

                    $equipment = Equipment::updateOrCreate(
                        ['code' => $eqCode],
                        [
                            'name' => $row['name'],
                            'equipment_model_id' => $modelId,
                            'category_id' => $catId,
                            'manufacturer_id' => $mfgId,
                            'serial_number' => null,
                            'serial_pending' => true,
                            'manufacture_year' => $row['year'],
                            'origin_country' => $row['country'],
                            'department_id' => $department->id,
                            'status' => EquipmentStatusEnum::DANG_SU_DUNG,
                            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
                            'ownership_type' => $row['ownership_type'],
                            'origin_model_text' => $row['col_e'],
                            'source_ref' => $sourceRef,
                            'import_batch_id' => $batch->id,
                            'note' => "ĐVT: {$row['unit']} | Nhập từ Phụ lục 2 (Dòng {$row['stt']})",
                        ]
                    );

                    // Tạo/Cập nhật 8 bản ghi equipment_documents cho từng thiết bị
                    $docMap = [
                        DocumentTypeEnum::HOP_DONG_HOA_DON->value => $row['doc_statuses']['hop_dong_hoa_don'],
                        DocumentTypeEnum::BIEN_BAN_BAN_GIAO->value => $row['doc_statuses']['bien_ban_ban_giao'],
                        DocumentTypeEnum::HO_SO_LUU_HANH->value => $row['doc_statuses']['ho_so_luu_hanh'],
                        DocumentTypeEnum::CO->value => $row['doc_statuses']['co'],
                        DocumentTypeEnum::CQ->value => $row['doc_statuses']['cq'],
                        DocumentTypeEnum::TO_KHAI_HAI_QUAN->value => $row['doc_statuses']['to_khai_hai_quan'],
                        DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD->value => $row['doc_statuses']['tai_lieu_ky_thuat_hdsd'],
                        DocumentTypeEnum::HO_SO_CONG_BO_DIEU_KIEN_MUA_BAN->value => $row['doc_statuses']['ho_so_cong_bo_dieu_kien_mua_ban'],
                    ];

                    foreach ($docMap as $docTypeVal => $statusEnum) {
                        EquipmentDocument::updateOrCreate(
                            [
                                'equipment_id' => $equipment->id,
                                'document_type' => $docTypeVal,
                            ],
                            [
                                'status' => $statusEnum->value,
                            ]
                        );
                    }
                }
            }
            $this->info("✓ Đã ghi CSDL thành công cho {$totalUnits} đơn vị thiết bị!");
        }

        // BÁO CÁO KẾT QUẢ CUỐI CÙNG
        $this->newLine();
        $this->line('================================================================');
        $this->info('                 BÁO CÁO KẾT QUẢ IMPORT PHỤ LỤC 2              ');
        $this->line('================================================================');
        $this->line(' Chế độ thực thi                : '.($isCommit ? 'COMMIT (Đã ghi CSDL)' : 'DRY-RUN (Chưa ghi CSDL)'));
        $rowCount = count($parsedRows);
        $this->line(" Tổng số dòng STT đã đọc        : <fg=cyan>{$rowCount}</>");
        $this->line(" Tổng số đơn vị thiết bị        : <fg=cyan>{$totalUnits}</> (kỳ vọng 286)");
        $this->line(" Số dòng có Số lượng > 1        : <fg=yellow>{$qtyGt1Count}</>");
        $this->line(" Số dòng có độ tin cậy THẤP     : <fg=yellow>{$lowConfCount}</> (cần xem lại CSV)");
        $this->line(" Số dòng TB Việt Nam có HS 'Không': <fg=magenta>{$domesticImportNoCount}</> (tự quyết định)");
        $this->line(" File báo cáo review CSV        : <fg=green>{$reviewCsvPath}</>");
        $this->line('================================================================');

        $this->newLine();
        $this->info('--- THỐNG KÊ CHI TIẾT 8 HỒ SƠ GIẤY TỜ (CỘT F - M) ---');

        $tableHeaders = ['Loại hồ sơ', 'Có', 'Không', 'Chưa rà soát', 'Không áp dụng'];
        $tableRows = [];

        $docLabels = [
            'hop_dong_hoa_don' => 'F. Hợp đồng, hóa đơn',
            'bien_ban_ban_giao' => 'G. Biên bản bàn giao',
            'ho_so_luu_hanh' => 'H. Hồ sơ lưu hành',
            'co' => 'I. CO (Chứng nhận xuất xứ)',
            'cq' => 'J. CQ (Chứng nhận chất lượng)',
            'to_khai_hai_quan' => 'K. Tờ khai hải quan',
            'tai_lieu_ky_thuat_hdsd' => 'L. Tài liệu kỹ thuật / HDSD',
            'ho_so_cong_bo_dieu_kien_mua_ban' => 'M. Công bố đủ ĐK mua bán',
        ];

        foreach ($docStats as $key => $counts) {
            $tableRows[] = [
                $docLabels[$key],
                $counts['co'],
                $counts['khong'],
                $counts['chua_ra_soat'],
                $counts['khong_ap_dung'],
            ];
        }

        $this->table($tableHeaders, $tableRows);

        return Command::SUCCESS;
    }

    private function parseColumnE(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['country' => null, 'year' => null, 'model' => null, 'mfg' => null, 'confidence' => 'thap'];
        }

        // 1. Nhận diện quốc gia
        $foundCountry = null;
        $matchedAliasStr = null;

        foreach ($this->countryAlias as $alias => $canonicalCountry) {
            if (preg_match('/\b'.preg_quote($alias, '/').'\b/ui', $text)) {
                $foundCountry = $canonicalCountry;
                $matchedAliasStr = $alias;
                break;
            }
        }

        if (! $foundCountry) {
            foreach ($this->countryAlias as $alias => $canonicalCountry) {
                if (mb_stripos($text, $alias) !== false) {
                    $foundCountry = $canonicalCountry;
                    $matchedAliasStr = $alias;
                    break;
                }
            }
        }

        // 2. Nhận diện năm sản xuất
        $foundYear = null;
        if (preg_match('/\b(19\d{2}|20\d{2})\b/', $text, $m)) {
            $foundYear = (int) $m[1];
        }

        // 3. Làm sạch text để bóc tách Model & Hãng
        $clean = $text;
        if ($matchedAliasStr) {
            $clean = preg_replace('/'.preg_quote($matchedAliasStr, '/').'/ui', '', $clean);
        }
        if ($foundYear) {
            $clean = preg_replace('/\b'.$foundYear.'\b/', '', $clean);
        }
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        // 4. Bóc tách Model & Hãng sản xuất
        $tokens = array_values(array_filter(explode(' ', $clean)));
        $foundModel = null;
        $mfgTokens = [];

        foreach ($tokens as $token) {
            if (! $foundModel && preg_match('/\d/', $token)) {
                $foundModel = $token;
            } else {
                $mfgTokens[] = $token;
            }
        }

        $foundMfg = trim(implode(' ', $mfgTokens));

        if (! $foundModel && ! empty($mfgTokens)) {
            $foundModel = array_shift($mfgTokens);
            $foundMfg = trim(implode(' ', $mfgTokens));
        }

        $confidence = 'cao';
        if (! $foundCountry || empty($foundModel) || (empty($foundMfg) && strlen($clean) < 5)) {
            $confidence = 'thap';
        }

        return [
            'country' => $foundCountry,
            'year' => $foundYear,
            'model' => $foundModel,
            'mfg' => $foundMfg,
            'confidence' => $confidence,
        ];
    }

    private function parseDocStatus(?string $val): DocumentStatusEnum
    {
        if ($val === null || trim($val) === '') {
            return DocumentStatusEnum::CHUA_RA_SOAT;
        }

        $clean = mb_strtolower(trim($val));
        if (str_contains($clean, 'có') || $clean === 'x' || $clean === '✓' || $clean === 'co') {
            return DocumentStatusEnum::CO;
        }

        if (str_contains($clean, 'không áp dụng') || str_contains($clean, 'khong ap dung')) {
            return DocumentStatusEnum::KHONG_AP_DUNG;
        }

        if (str_contains($clean, 'không') || str_contains($clean, 'khong')) {
            return DocumentStatusEnum::KHONG;
        }

        return DocumentStatusEnum::CHUA_RA_SOAT;
    }
}
