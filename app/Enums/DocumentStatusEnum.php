<?php

namespace App\Enums;

enum DocumentStatusEnum: string
{
    case CO = 'co';
    case KHONG = 'khong';
    case KHONG_AP_DUNG = 'khong_ap_dung';
    case CHUA_RA_SOAT = 'chua_ra_soat';

    public function label(): string
    {
        return match ($this) {
            self::CO => 'Có',
            self::KHONG => 'Không',
            self::KHONG_AP_DUNG => 'Không áp dụng',
            self::CHUA_RA_SOAT => 'Chưa rà soát',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CO => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::KHONG => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            self::KHONG_AP_DUNG => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
            self::CHUA_RA_SOAT => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        };
    }
}
