<?php

namespace App\Enums;

enum OwnershipTypeEnum: string
{
    case MUA_SAM = 'mua_sam';
    case MAY_DAT = 'may_dat';
    case MUON = 'muon';
    case TAI_TRO = 'tai_tro';

    public function label(): string
    {
        return match ($this) {
            self::MUA_SAM => 'Mua sắm',
            self::MAY_DAT => 'Máy đặt của nhà cung cấp',
            self::MUON => 'Mượn',
            self::TAI_TRO => 'Tài trợ',
        };
    }
}
