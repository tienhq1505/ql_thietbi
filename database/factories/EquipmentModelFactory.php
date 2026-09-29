<?php

namespace Database\Factories;

use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentModelFactory extends Factory
{
    protected $model = EquipmentModel::class;

    public function definition(): array
    {
        return [
            'code' => 'MOD-'.$this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->word().' '.$this->faker->regexify('[A-Z]{2}-[0-9]{3}'),
            'category_id' => EquipmentCategory::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'specs' => $this->faker->sentence(),
        ];
    }
}
