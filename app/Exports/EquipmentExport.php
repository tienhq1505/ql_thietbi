<?php

namespace App\Exports;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EquipmentExport implements FromQuery, WithColumnFormatting, WithCustomCsvSettings, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Mã thiết bị',
            'Tên thiết bị',
            'Mã model',
            'Tên model',
            'Loại thiết bị',
            'Hãng sản xuất',
            'Khoa phòng',
            'Vị trí',
            'Số serial',
            'Loại sở hữu',
            'Năm sản xuất',
            'Xuất xứ',
            'Ngày nhập',
            'Ngày sử dụng',
            'Giá mua (VNĐ)',
            'Nguồn kinh phí',
            'Nhà cung cấp',
            'Hạn bảo hành',
            'Người phụ trách',
            'Trạng thái',
            'Tình trạng',
            'Ghi chú',
        ];
    }

    public function map($item): array
    {
        /** @var Equipment $item */
        return [
            $item->code,
            $item->name,
            $item->equipmentModel?->code ?? '',
            $item->equipmentModel?->name ?? '',
            $item->category?->name ?? '',
            $item->manufacturer?->name ?? '',
            $item->department?->name ?? '',
            $item->location?->name ?? '',
            $item->serial_number ?: 'Chưa có serial',
            $item->ownership_type?->label() ?? $item->ownership_type ?? '',
            $item->manufacture_year ?? '',
            $item->origin_country ?? '',
            $item->import_date ? $item->import_date->format('d/m/Y') : '',
            $item->usage_start_date ? $item->usage_start_date->format('d/m/Y') : '',
            $item->purchase_price ? (float) $item->purchase_price : 0,
            $item->funding_source ?? '',
            $item->supplier?->name ?? '',
            $item->warranty_expiration_date ? $item->warranty_expiration_date->format('d/m/Y') : '',
            $item->manager_name ?? $item->manager?->name ?? '',
            $item->status?->label() ?? $item->status ?? '',
            $item->condition?->label() ?? $item->condition ?? '',
            $item->note ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'O' => '#,##0',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }
}
