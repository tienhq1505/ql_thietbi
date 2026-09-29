<?php

namespace App\Enums;

enum EquipmentConditionEnum: string
{
    case DANG_HOAT_DONG = 'dang_hoat_dong';
    case CAN_THEO_DOI = 'can_theo_doi';
    case CAN_BAO_TRI = 'can_bao_tri';
    case HONG = 'hong';
    case NGUNG_SU_DUNG = 'ngung_su_dung';

    public function label(): string
    {
        return match ($this) {
            self::DANG_HOAT_DONG => 'Đang hoạt động',
            self::CAN_THEO_DOI => 'Cần theo dõi',
            self::CAN_BAO_TRI => 'Cần bảo trì',
            self::HONG => 'Hỏng',
            self::NGUNG_SU_DUNG => 'Ngừng sử dụng',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DANG_HOAT_DONG => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::CAN_THEO_DOI => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::CAN_BAO_TRI => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
            self::HONG => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            self::NGUNG_SU_DUNG => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }
}
