<?php

namespace Database\Factories;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Equipment;
use App\Models\EquipmentDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentDocumentFactory extends Factory
{
    protected $model = EquipmentDocument::class;

    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'document_type' => $this->faker->randomElement(DocumentTypeEnum::cases()),
            'status' => $this->faker->randomElement(DocumentStatusEnum::cases()),
            'file_path' => null,
            'valid_until' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
