<?php

namespace App\Enums;

enum EquipmentStatusEnum: string
{
    case DANG_SU_DUNG = 'dang_su_dung';
    case DU_PHONG = 'du_phong';
    case THANH_LY = 'thanh_ly';
    case DA_THANH_LY = 'da_thanh_ly';

    public function label(): string
    {
        return match ($this) {
            self::DANG_SU_DUNG => 'Đang sử dụng',
            self::DU_PHONG => 'Dự phòng',
            self::THANH_LY => 'Đề nghị thanh lý',
            self::DA_THANH_LY => 'Đã thanh lý',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DANG_SU_DUNG => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
            self::DU_PHONG => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            self::THANH_LY => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::DA_THANH_LY => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        };
    }
}
