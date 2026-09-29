<?php

namespace App\Exports;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Department;
use App\Models\EquipmentDocument;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocumentReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected ?int $departmentId;

    public function __construct(?int $departmentId = null)
    {
        $this->departmentId = $departmentId;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên khoa / phòng',
            'Lý lịch thiết bị',
            'Quyết định / Biên bản bàn giao',
            'Hồ sơ lưu hành',
            'CO (Xuất xứ)',
            'CQ (Chất lượng)',
            'Tờ khai hải quan',
            'Tài liệu KT / HDSD',
            'Công bố đủ ĐK mua bán',
            'Nhật ký sử dụng máy',
            'Nội quy / Quy trình vận hành',
            'Hồ sơ bảo dưỡng',
        ];
    }

    public function collection(): Enumerable
    {
        $docTypes = [
            DocumentTypeEnum::LY_LICH,
            DocumentTypeEnum::BIEN_BAN_BAN_GIAO,
            DocumentTypeEnum::HO_SO_LUU_HANH,
            DocumentTypeEnum::CO,
            DocumentTypeEnum::CQ,
            DocumentTypeEnum::TO_KHAI_HAI_QUAN,
            DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD,
            DocumentTypeEnum::HO_SO_CONG_BO_DIEU_KIEN_MUA_BAN,
            DocumentTypeEnum::NHAT_KY_SU_DUNG,
            DocumentTypeEnum::QUY_TRINH_VAN_HANH,
            DocumentTypeEnum::HO_SO_BAO_DUONG,
        ];

        $query = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->orderBy('name');
        if ($this->departmentId) {
            $query->where('id', $this->departmentId);
        }

        $departments = $query->get();
        $rows = collect();
        $stt = 1;

        $totalEq = 0;
        $totalDocs = [];
        foreach ($docTypes as $dt) {
            $totalDocs[$dt->value] = 0;
        }

        foreach ($departments as $dept) {
            $eqCount = $dept->equipment()->count();
            $totalEq += $eqCount;
            $eqIds = $dept->equipment()->pluck('id')->toArray();

            $row = [
                'stt' => $stt++,
                'name' => $dept->name,
            ];

            foreach ($docTypes as $dt) {
                $hasCount = 0;
                if (! empty($eqIds)) {
                    $hasCount = EquipmentDocument::whereIn('equipment_id', $eqIds)
                        ->where('document_type', $dt->value)
                        ->where('status', DocumentStatusEnum::CO->value)
                        ->count();
                }
                $totalDocs[$dt->value] += $hasCount;
                $row[$dt->value] = sprintf('%d/%d', $hasCount, $eqCount);
            }

            $rows->push($row);
        }

        // Summary Row TỔNG
        $totalRow = [
            'stt' => '',
            'name' => 'TỔNG',
        ];
        foreach ($docTypes as $dt) {
            $totalRow[$dt->value] = sprintf('%d/%d', $totalDocs[$dt->value], $totalEq);
        }
        $rows->push($totalRow);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            $sheet->getHighestRow() => ['font' => ['bold' => true]],
        ];
    }
}
