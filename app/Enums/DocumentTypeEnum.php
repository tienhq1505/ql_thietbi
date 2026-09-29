<?php

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case HOP_DONG_HOA_DON = 'hop_dong_hoa_don';
    case BIEN_BAN_BAN_GIAO = 'bien_ban_ban_giao';
    case HO_SO_LUU_HANH = 'ho_so_luu_hanh';
    case CO = 'co';
    case CQ = 'cq';
    case TO_KHAI_HAI_QUAN = 'to_khai_hai_quan';
    case TAI_LIEU_KY_THUAT_HDSD = 'tai_lieu_ky_thuat_hdsd';
    case HO_SO_CONG_BO_DIEU_KIEN_MUA_BAN = 'ho_so_cong_bo_dieu_kien_mua_ban';
    case LY_LICH = 'ly_lich';
    case NHAT_KY_SU_DUNG = 'nhat_ky_su_dung';
    case QUY_TRINH_VAN_HANH = 'quy_trinh_van_hanh';
    case HO_SO_BAO_DUONG = 'ho_so_bao_duong';
    case HO_SO_DIEU_CHUYEN_THANH_LY = 'ho_so_dieu_chuyen_thanh_ly';
    case KIEM_DINH = 'kiem_dinh';
    case HIEU_CHUAN = 'hieu_chuan';
    case PHAN_CONG_NGUOI_PHU_TRACH = 'phan_cong_nguoi_phu_trach';

    public function label(): string
    {
        return match ($this) {
            self::HOP_DONG_HOA_DON => 'Hợp đồng, hóa đơn',
            self::BIEN_BAN_BAN_GIAO => 'Biên bản bàn giao',
            self::HO_SO_LUU_HANH => 'Hồ sơ lưu hành',
            self::CO => 'CO (Chứng nhận xuất xứ)',
            self::CQ => 'CQ (Chứng nhận chất lượng)',
            self::TO_KHAI_HAI_QUAN => 'Tờ khai hải quan',
            self::TAI_LIEU_KY_THUAT_HDSD => 'Tài liệu kỹ thuật / HDSD',
            self::HO_SO_CONG_BO_DIEU_KIEN_MUA_BAN => 'Hồ sơ công bố điều kiện mua bán',
            self::LY_LICH => 'Lý lịch thiết bị',
            self::NHAT_KY_SU_DUNG => 'Nhật ký sử dụng',
            self::QUY_TRINH_VAN_HANH => 'Quy trình vận hành',
            self::HO_SO_BAO_DUONG => 'Hồ sơ bảo dưỡng',
            self::HO_SO_DIEU_CHUYEN_THANH_LY => 'Hồ sơ điều chuyển / thanh lý',
            self::KIEM_DINH => 'Hồ sơ kiểm định',
            self::HIEU_CHUAN => 'Hồ sơ hiệu chuẩn',
            self::PHAN_CONG_NGUOI_PHU_TRACH => 'Phân công người phụ trách',
        };
    }
}
