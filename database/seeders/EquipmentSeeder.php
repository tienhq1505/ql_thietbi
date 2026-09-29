<?php

namespace Database\Seeders;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentConditionHistory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $models = EquipmentModel::all();
        $departments = Department::all();
        $suppliers = Supplier::all();
        $users = User::all();

        if ($models->isEmpty() || $departments->isEmpty()) {
            return;
        }

        // Realistic equipment assets definitions for structured seeding
        $presetAssets = [
            // Hồi sức tích cực
            ['code' => 'TB-2024-0001', 'name' => 'Máy thở cao cấp Evita V800 - ICU 01', 'model_code' => 'MOD-EVITA-V800', 'dept_code' => 'K-HSTC', 'year' => 2023, 'price' => 850000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0002', 'name' => 'Máy thở cao cấp Evita V800 - ICU 02', 'model_code' => 'MOD-EVITA-V800', 'dept_code' => 'K-HSTC', 'year' => 2023, 'price' => 850000000, 'condition' => EquipmentConditionEnum::CAN_THEO_DOI, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0003', 'name' => 'Monitor theo dõi bệnh nhân MX450 #1', 'model_code' => 'MOD-MX450', 'dept_code' => 'K-HSTC', 'year' => 2022, 'price' => 120000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0004', 'name' => 'Monitor theo dõi bệnh nhân MX450 #2', 'model_code' => 'MOD-MX450', 'dept_code' => 'K-HSTC', 'year' => 2022, 'price' => 120000000, 'condition' => EquipmentConditionEnum::CAN_BAO_TRI, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],

            // Cấp cứu
            ['code' => 'TB-2024-0010', 'name' => 'Monitor bệnh nhân uMEC12 Cấp cứu #1', 'model_code' => 'MOD-uMEC12', 'dept_code' => 'K-CC', 'year' => 2021, 'price' => 45000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0011', 'name' => 'Monitor bệnh nhân uMEC12 Cấp cứu #2', 'model_code' => 'MOD-uMEC12', 'dept_code' => 'K-CC', 'year' => 2021, 'price' => 45000000, 'condition' => EquipmentConditionEnum::HONG, 'status' => EquipmentStatusEnum::DU_PHONG],
            ['code' => 'TB-2024-0012', 'name' => 'Bơm tiêm điện Perfusor Space Cấp cứu', 'model_code' => 'MOD-PERFUSOR-SPACE', 'dept_code' => 'K-CC', 'year' => 2023, 'price' => 35000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],

            // Khoa Nội
            ['code' => 'TB-2024-0020', 'name' => 'Máy điện tim 12 kênh Cardiofax M - Khoa Nội', 'model_code' => 'MOD-CARDIOFAX-M', 'dept_code' => 'K-NOI', 'year' => 2020, 'price' => 65000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0021', 'name' => 'Bơm tiêm điện Perfusor Space Khoa Nội #1', 'model_code' => 'MOD-PERFUSOR-SPACE', 'dept_code' => 'K-NOI', 'year' => 2022, 'price' => 35000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0022', 'name' => 'Monitor uMEC12 Khoa Nội #2', 'model_code' => 'MOD-uMEC12', 'dept_code' => 'K-NOI', 'year' => 2019, 'price' => 45000000, 'condition' => EquipmentConditionEnum::NGUNG_SU_DUNG, 'status' => EquipmentStatusEnum::THANH_LY],

            // Chẩn đoán hình ảnh
            ['code' => 'TB-2024-0030', 'name' => 'Máy siêu âm màu 4D Voluson E10 - CĐHA 01', 'model_code' => 'MOD-VOLUSON-E10', 'dept_code' => 'K-CDHA', 'year' => 2023, 'price' => 1850000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0031', 'name' => 'Máy X-quang kỹ thuật số FDR Smart f', 'model_code' => 'MOD-FDR-SMART', 'dept_code' => 'K-CDHA', 'year' => 2021, 'price' => 1250000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0032', 'name' => 'Máy chụp CT-Scanner SOMATOM Go.Up 32 dãy', 'model_code' => 'MOD-SOMATOM-GO', 'dept_code' => 'K-CDHA', 'year' => 2022, 'price' => 4200000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],

            // Xét nghiệm
            ['code' => 'TB-2024-0040', 'name' => 'Máy xét nghiệm huyết học tự động XN-550', 'model_code' => 'MOD-XN-550', 'dept_code' => 'K-XN', 'year' => 2022, 'price' => 480000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
            ['code' => 'TB-2024-0041', 'name' => 'Máy xét nghiệm sinh hóa BS-240', 'model_code' => 'MOD-BS-240', 'dept_code' => 'K-XN', 'year' => 2021, 'price' => 380000000, 'condition' => EquipmentConditionEnum::CAN_THEO_DOI, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],

            // Khoa Sản
            ['code' => 'TB-2024-0050', 'name' => 'Lồng ấp sơ sinh Isolette 8000 plus', 'model_code' => 'MOD-ISOLETTE-8000', 'dept_code' => 'K-SAN', 'year' => 2023, 'price' => 260000000, 'condition' => EquipmentConditionEnum::DANG_HOAT_DONG, 'status' => EquipmentStatusEnum::DANG_SU_DUNG],
        ];

        $modelMap = $models->keyBy('code');
        $deptMap = $departments->keyBy('code');

        foreach ($presetAssets as $index => $item) {
            $model = $modelMap[$item['model_code']] ?? $models->first();
            $dept = $deptMap[$item['dept_code']] ?? $departments->first();
            $supplier = $suppliers->random();
            $manager = $users->where('department_id', $dept->id)->first() ?? $users->first();
            $location = Location::where('department_id', $dept->id)->first();

            Equipment::updateOrCreate(['code' => $item['code']], [
                'name' => $item['name'],
                'equipment_model_id' => $model->id,
                'category_id' => $model->category_id,
                'manufacturer_id' => $model->manufacturer_id,
                'serial_number' => 'SN-2024-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'manufacture_year' => $item['year'],
                'origin_country' => $model->manufacturer->country ?? 'Mỹ',
                'import_date' => now()->subMonths(12)->format('Y-m-d'),
                'usage_start_date' => now()->subMonths(11)->format('Y-m-d'),
                'purchase_price' => $item['price'],
                'funding_source' => 'Ngân sách Nhà nước',
                'supplier_id' => $supplier?->id,
                'warranty_expiration_date' => now()->addMonths(24)->format('Y-m-d'),
                'department_id' => $dept->id,
                'location_id' => $location?->id,
                'manager_id' => $manager?->id,
                'manager_name' => $manager?->name ?? 'Người quản lý khoa',
                'status' => $item['status'],
                'condition' => $item['condition'],
                'note' => 'Thiết bị bàn giao theo hợp đồng thiết bị y tế '.$item['year'],
            ]);
        }

        // Fill up to ~110 items with Factory generated items
        $currentCount = Equipment::count();
        if ($currentCount < 110) {
            $needed = 110 - $currentCount;
            for ($i = 0; $i < $needed; $i++) {
                $dept = $departments->random();
                $model = $models->random();
                $supplier = $suppliers->random();
                $location = Location::where('department_id', $dept->id)->first() ?? Location::first();
                $manager = $users->where('department_id', $dept->id)->first() ?? $users->first();

                Equipment::factory()->create([
                    'code' => 'TB-GEN-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'equipment_model_id' => $model->id,
                    'category_id' => $model->category_id,
                    'manufacturer_id' => $model->manufacturer_id,
                    'department_id' => $dept->id,
                    'location_id' => $location?->id,
                    'supplier_id' => $supplier?->id,
                    'manager_id' => $manager?->id,
                    'manager_name' => $manager?->name,
                ]);
            }
        }

        // Create initial condition history entries for demo
        $sampleEquipments = Equipment::take(10)->get();
        $adminUser = User::first();
        foreach ($sampleEquipments as $eq) {
            EquipmentConditionHistory::firstOrCreate([
                'equipment_id' => $eq->id,
                'new_condition' => $eq->condition->value,
            ], [
                'old_condition' => EquipmentConditionEnum::DANG_HOAT_DONG->value,
                'reason' => 'Kiểm tra kỹ thuật định kỳ bàn giao thiết bị',
                'note' => 'Bản ghi khởi tạo từ seeder',
                'checked_by' => $adminUser?->id ?? 1,
                'checked_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
