<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    public function definition(): array
    {
        return [
            'code' => 'MFG-'.$this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->company(),
            'country' => $this->faker->country(),
            'website' => $this->faker->url(),
        ];
    }
}
