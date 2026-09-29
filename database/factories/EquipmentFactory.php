<?php

namespace Database\Factories;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'code' => 'TB-'.$this->faker->unique()->numberBetween(10000, 99999),
            'name' => $this->faker->words(3, true),
            'equipment_model_id' => EquipmentModel::factory(),
            'category_id' => EquipmentCategory::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'serial_number' => 'SN-'.strtoupper($this->faker->unique()->bothify('??###??###')),
            'manufacture_year' => $this->faker->numberBetween(2015, 2025),
            'origin_country' => $this->faker->randomElement(['Mỹ', 'Đức', 'Nhật Bản', 'Hà Lan', 'Thụy Sĩ', 'Hàn Quốc', 'Trung Quốc']),
            'import_date' => $this->faker->dateTimeBetween('-5 years', '-1 years')->format('Y-m-d'),
            'usage_start_date' => $this->faker->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
            'purchase_price' => $this->faker->randomElement([15000000, 45000000, 120000000, 350000000, 850000000, 1500000000]),
            'funding_source' => $this->faker->randomElement(['Ngân sách Nhà nước', 'BHYT', 'Quỹ phát triển hoạt động sự nghiệp', 'Tài trợ / Viện trợ']),
            'supplier_id' => Supplier::factory(),
            'warranty_expiration_date' => $this->faker->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
            'department_id' => Department::factory(),
            'location_id' => Location::factory(),
            'manager_name' => $this->faker->name(),
            'status' => EquipmentStatusEnum::DANG_SU_DUNG,
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
