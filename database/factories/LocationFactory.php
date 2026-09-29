<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'code' => 'LOC-'.$this->faker->unique()->numberBetween(100, 999),
            'name' => 'Phòng '.$this->faker->numberBetween(101, 509),
            'department_id' => Department::factory(),
            'building' => 'Tòa nhà '.$this->faker->randomElement(['A', 'B', 'C', 'Khu Kỹ Thuật']),
            'floor' => 'Tầng '.$this->faker->numberBetween(1, 7),
            'room' => 'P.'.$this->faker->numberBetween(101, 509),
        ];
    }
}
