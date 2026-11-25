<?php

namespace App\Enums;

enum InventoryItemResultEnum: string
{
    case KHOP = 'khop';
    case THIEU = 'thieu';
    case THUA = 'thua';
    case HONG = 'hong';

    public function label(): string
    {
        return match ($this) {
            self::KHOP => 'Khớp',
            self::THIEU => 'Thiếu',
            self::THUA => 'Thừa',
            self::HONG => 'Hỏng',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::KHOP => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            self::THIEU => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
            self::THUA => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            self::HONG => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        };
    }
}
