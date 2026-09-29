<?php

namespace Database\Factories;

use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentCategoryFactory extends Factory
{
    protected $model = EquipmentCategory::class;

    public function definition(): array
    {
        return [
            'code' => 'CAT-'.$this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
