<?php

namespace Tests\Feature;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\OwnershipTypeEnum;
use App\Livewire\Equipment\EquipmentCreate;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentRealDataSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected Department $otherDepartment;

    protected Location $location;

    protected Location $otherLocation;

    protected EquipmentModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->department = Department::create(['code' => 'KNOI', 'name' => 'Khoa Nội']);
        $this->otherDepartment = Department::create(['code' => 'KSAN', 'name' => 'Khoa Sản']);

        $this->location = Location::create([
            'code' => 'LOC-KNOI-01',
            'name' => 'Phòng Nội 1',
            'department_id' => $this->department->id,
        ]);

        $this->otherLocation = Location::create([
            'code' => 'LOC-KSAN-01',
            'name' => 'Phòng Sản 1',
            'department_id' => $this->otherDepartment->id,
        ]);

        $category = EquipmentCategory::create(['code' => 'CAT-01', 'name' => 'Loại 1']);
        $manufacturer = Manufacturer::create(['code' => 'MFG-01', 'name' => 'Hãng 1']);

        $this->model = EquipmentModel::create([
            'code' => 'MOD-01',
            'name' => 'Model 01',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);
    }

    public function test_serial_number_can_be_null_and_multiple_nulls_are_allowed(): void
    {
        $eq1 = Equipment::create([
            'code' => 'TB-NULL-001',
            'name' => 'Thiết bị không serial 1',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => null,
            'serial_pending' => true,
        ]);

        $eq2 = Equipment::create([
            'code' => 'TB-NULL-002',
            'name' => 'Thiết bị không serial 2',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => null,
            'serial_pending' => true,
        ]);

        $this->assertDatabaseCount('equipment', 2);
        $this->assertNull($eq1->serial_number);
        $this->assertNull($eq2->serial_number);
        $this->assertTrue($eq1->serial_pending);
        $this->assertTrue($eq2->serial_pending);
    }

    public function test_unique_serial_number_enforced_when_non_null(): void
    {
        Equipment::create([
            'code' => 'TB-SN-001',
            'name' => 'Thiết bị serial 1',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => 'SN-UNIQUE-999',
        ]);

        $this->expectException(QueryException::class);

        Equipment::create([
            'code' => 'TB-SN-002',
            'name' => 'Thiết bị trùng serial',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => 'SN-UNIQUE-999',
        ]);
    }

    public function test_equipment_documents_unique_constraint_on_equipment_id_and_document_type(): void
    {
        $eq = Equipment::create([
            'code' => 'TB-DOC-001',
            'name' => 'Thiết bị hồ sơ',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => 'SN-DOC-001',
        ]);

        EquipmentDocument::create([
            'equipment_id' => $eq->id,
            'document_type' => DocumentTypeEnum::HOP_DONG_HOA_DON,
            'status' => DocumentStatusEnum::CO,
        ]);

        $this->expectException(QueryException::class);

        EquipmentDocument::create([
            'equipment_id' => $eq->id,
            'document_type' => DocumentTypeEnum::HOP_DONG_HOA_DON,
            'status' => DocumentStatusEnum::KHONG,
        ]);
    }

    public function test_location_must_belong_to_department_in_livewire_create(): void
    {
        $admin = User::factory()->create(['department_id' => $this->department->id]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(EquipmentCreate::class)
            ->set('code', 'TB-LOC-TEST')
            ->set('name', 'Thiết bị vị trí sai')
            ->set('equipment_model_id', $this->model->id)
            ->set('category_id', $this->model->category_id)
            ->set('manufacturer_id', $this->model->manufacturer_id)
            ->set('department_id', $this->department->id)
            ->set('location_id', $this->otherLocation->id) // Location belongs to otherDepartment!
            ->call('save')
            ->assertHasErrors(['location_id']);
    }

    public function test_ownership_type_and_serial_pending_defaults(): void
    {
        $eq = Equipment::create([
            'code' => 'TB-OWN-001',
            'name' => 'Thiết bị sở hữu',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => null,
            'ownership_type' => OwnershipTypeEnum::MAY_DAT,
        ]);

        $this->assertEquals(OwnershipTypeEnum::MAY_DAT, $eq->ownership_type);
        $this->assertEquals('Máy đặt của nhà cung cấp', $eq->ownership_type->label());
    }
}
