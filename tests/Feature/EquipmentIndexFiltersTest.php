<?php

namespace Tests\Feature;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\OwnershipTypeEnum;
use App\Livewire\Equipment\EquipmentIndex;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    private EquipmentCategory $category;

    private Manufacturer $manufacturer;

    private EquipmentModel $model;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->dept = Department::first();
        $this->category = EquipmentCategory::create(['code' => 'CAT-FIL', 'name' => 'Loại filter']);
        $this->manufacturer = Manufacturer::create(['code' => 'MFG-FIL', 'name' => 'Hãng filter']);
        $this->model = EquipmentModel::create([
            'code' => 'MOD-FIL',
            'name' => 'Model filter',
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    public function test_filter_by_missing_document_and_missing_serial(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        // Eq 1: Has document CO
        $eq1 = Equipment::create([
            'code' => 'TB-FIL-01',
            'name' => 'Thiết bị đầy đủ',
            'serial_number' => 'SN-1001',
            'serial_pending' => false,
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->dept->id,
            'ownership_type' => OwnershipTypeEnum::MUA_SAM,
        ]);

        EquipmentDocument::create([
            'equipment_id' => $eq1->id,
            'document_type' => DocumentTypeEnum::LY_LICH->value,
            'status' => DocumentStatusEnum::CO->value,
        ]);

        // Eq 2: Missing document CO and missing serial
        $eq2 = Equipment::create([
            'code' => 'TB-FIL-02',
            'name' => 'Thiết bị thiếu thông tin',
            'serial_number' => null,
            'serial_pending' => true,
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->dept->id,
            'ownership_type' => OwnershipTypeEnum::MAY_DAT,
        ]);

        // Test missing document filter
        Livewire::actingAs($admin)
            ->test(EquipmentIndex::class)
            ->set('missing_doc_type', DocumentTypeEnum::LY_LICH->value)
            ->assertSee('TB-FIL-02')
            ->assertDontSee('TB-FIL-01');

        // Test missing serial filter
        Livewire::actingAs($admin)
            ->test(EquipmentIndex::class)
            ->set('missing_serial', '1')
            ->assertSee('TB-FIL-02')
            ->assertDontSee('TB-FIL-01');

        // Test ownership type filter
        Livewire::actingAs($admin)
            ->test(EquipmentIndex::class)
            ->set('ownership_type', OwnershipTypeEnum::MAY_DAT->value)
            ->assertSee('TB-FIL-02')
            ->assertDontSee('TB-FIL-01');
    }
}
