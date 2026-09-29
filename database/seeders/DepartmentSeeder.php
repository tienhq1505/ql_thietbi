<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Dữ liệu thật: 16 khoa/phòng lâm sàng + 3 đơn vị hệ thống = 19 bản ghi.
     */
    public function run(): void
    {
        $departments = [
            // ── 16 khoa/phòng lâm sàng thật ───────────────────────────────
            [
                'code' => 'KGMHS',
                'name' => 'Khoa Gây mê hồi sức',
                'short_name' => 'Gây mê HS',
            ],
            [
                'code' => 'KTNT',
                'name' => 'Khoa Thận nhân tạo',
                'short_name' => 'Thận nhân tạo',
            ],
            [
                'code' => 'KHSCC',
                'name' => 'Khoa Hồi sức cấp cứu',
                'short_name' => 'Hồi sức CC',
            ],
            [
                'code' => 'KKB',
                'name' => 'Khoa Khám bệnh',
                'short_name' => 'Khám bệnh',
            ],
            [
                'code' => 'KNOAI3A',
                'name' => 'Khoa Ngoại CT tầng 3 Khu A',
                'short_name' => 'Ngoại T3 KhuA',
            ],
            [
                'code' => 'KNOAI4A',
                'name' => 'Khoa Ngoại CT tầng 4 Khu A',
                'short_name' => 'Ngoại T4 KhuA',
            ],
            [
                'code' => 'KNOI',
                'name' => 'Khoa Nội',
                'short_name' => 'Khoa Nội',
            ],
            [
                'code' => 'KSAN',
                'name' => 'Khoa Sản',
                'short_name' => 'Khoa Sản',
            ],
            [
                'code' => 'KXN',
                'name' => 'Khoa Xét nghiệm',
                'short_name' => 'Xét nghiệm',
            ],
            [
                'code' => 'KYHCT',
                'name' => 'Khoa Y học cổ truyền',
                'short_name' => 'Y học CT',
            ],
            [
                'code' => 'KMAT',
                'name' => 'Khoa Mắt',
                'short_name' => 'Khoa Mắt',
            ],
            [
                'code' => 'KRHM',
                'name' => 'Khoa Răng hàm mặt',
                'short_name' => 'Răng hàm mặt',
            ],
            [
                'code' => 'KTMH',
                'name' => 'Khoa Tai mũi họng',
                'short_name' => 'Tai mũi họng',
            ],
            [
                'code' => 'PTIEM',
                'name' => 'Phòng Tiêm chủng',
                'short_name' => 'Tiêm chủng',
            ],
            [
                'code' => 'KNOISOI',
                'name' => 'Khoa Nội soi',
                'short_name' => 'Nội soi',
            ],
            [
                'code' => 'KCDHA',
                'name' => 'Khoa Chẩn đoán hình ảnh',
                'short_name' => 'CĐHA',
            ],

            // ── 3 đơn vị hệ thống ──────────────────────────────────────────
            [
                'code' => 'PVTTBYT',
                'name' => 'Phòng Vật tư-TBYT',
                'short_name' => 'Phòng VT-TBYT',
            ],
            [
                'code' => 'BGD',
                'name' => 'Ban Giám đốc',
                'short_name' => 'Ban GĐ',
            ],
            [
                'code' => 'CHUA-PHAN-KHOA',
                'name' => 'Chưa phân khoa',
                'short_name' => 'Chưa phân khoa',
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
