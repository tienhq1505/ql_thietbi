<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => 'K-'.strtoupper($this->faker->unique()->lexify('???')),
            'name' => 'Khoa '.$this->faker->word(),
            'short_name' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'head_name' => $this->faker->name(),
        ];
    }
}
