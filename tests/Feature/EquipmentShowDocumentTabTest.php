<?php

namespace Tests\Feature;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Livewire\Equipment\EquipmentShow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentShowDocumentTabTest extends TestCase
{
    use RefreshDatabase;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $dept = Department::first();
        $cat = EquipmentCategory::create(['code' => 'CAT-DOC', 'name' => 'Loại doc']);
        $mfg = Manufacturer::create(['code' => 'MFG-DOC', 'name' => 'Hãng doc']);
        $model = EquipmentModel::create([
            'code' => 'MOD-DOC',
            'name' => 'Model doc',
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
        ]);

        $this->equipment = Equipment::create([
            'code' => 'TB-DOC-01',
            'name' => 'Thiết bị kiểm tra hồ sơ',
            'equipment_model_id' => $model->id,
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
            'department_id' => $dept->id,
        ]);
    }

    public function test_view_documents_checklist_and_unreviewed_badge(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->set('activeTab', 'documents')
            ->assertSee('Checklist Hồ sơ')
            ->assertSee('CHƯA RÀ SOÁT');
    }

    public function test_update_document_status(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->call('openEditDocument', DocumentTypeEnum::LY_LICH->value)
            ->set('docStatus', DocumentStatusEnum::CO->value)
            ->set('docValidUntil', '2026-12-31')
            ->set('docNote', 'Đã bổ sung lý lịch')
            ->call('saveDocument')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('equipment_documents', [
            'equipment_id' => $this->equipment->id,
            'document_type' => DocumentTypeEnum::LY_LICH->value,
            'status' => DocumentStatusEnum::CO->value,
            'note' => 'Đã bổ sung lý lịch',
        ]);
    }
}
