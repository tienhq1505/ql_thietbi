<?php

namespace App\Exports;

use App\Models\InventorySession;
use App\Models\User;
use App\Services\InventoryReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryDiscrepancyExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected InventorySession $session,
        protected User $user
    ) {}

    public function title(): string
    {
        return 'Chênh lệch kiểm kê';
    }

    public function headings(): array
    {
        return [
            'STT',
            'Mã quản lý / Quét',
            'Tên thiết bị',
            'Dòng máy / Model',
            'Số Serial',
            'Khoa phòng hệ thống',
            'Vị trí hệ thống',
            'Vị trí thực tế',
            'Kết quả kiểm kê',
            'Ghi chú / Tình trạng',
            'Người kiểm',
            'Thời gian kiểm',
        ];
    }

    public function collection(): Collection
    {
        $filteredItems = app(InventoryReportService::class)
            ->getDiscrepancyItems($this->session, $this->user);

        $stt = 1;

        return $filteredItems->map(function ($item) use (&$stt) {
            $eq = $item->equipment;

            return array_map([self::class, 'escapeFormula'], [
                'stt' => $stt++,
                'code' => $item->scanned_code ?: ($eq?->code ?? '-'),
                'name' => $eq?->name ?? 'Thiết bị không xác định',
                'model' => $eq?->equipmentModel?->name ?? ($eq?->equipmentModel?->code ?? '-'),
                'serial' => $eq?->serial_number ?: 'Chưa có serial',
                'dept' => $item->systemDepartment?->name ?? ($eq?->department?->name ?? '-'),
                'sys_location' => $item->systemLocation?->name ?? ($eq?->location?->name ?? '-'),
                'act_location' => $item->actual_location ?: '-',
                'result' => $item->result?->label() ?? '-',
                'note' => $item->note ?: '-',
                'checker' => $item->checker?->name ?? '-',
                'checked_at' => $item->checked_at ? $item->checked_at->format('d/m/Y H:i') : '-',
            ]);
        });
    }

    public static function escapeFormula(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = max(1, $sheet->getHighestRow());

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            "A1:L{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ],
        ];
    }
}
