<?php

namespace App\Enums;

enum InventorySessionStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Đang kiểm kê',
            self::COMPLETED => 'Đã chốt kỳ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20',
            self::COMPLETED => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        };
    }
}
