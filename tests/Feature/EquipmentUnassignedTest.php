<?php

namespace Tests\Feature;

use App\Livewire\Equipment\EquipmentUnassigned;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDepartmentHistory;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentUnassignedTest extends TestCase
{
    use RefreshDatabase;

    private Department $unassignedDept;

    private Department $targetDept;

    private EquipmentCategory $category;

    private Manufacturer $manufacturer;

    private EquipmentModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->unassignedDept = Department::where('code', 'CHUA-PHAN-KHOA')->firstOrFail();
        $this->targetDept = Department::where('code', 'KNOI')->firstOrFail();

        $this->category = EquipmentCategory::create(['code' => 'CAT-TEST', 'name' => 'Loại test']);
        $this->manufacturer = Manufacturer::create(['code' => 'MFG-TEST', 'name' => 'Hãng test', 'country' => 'Việt Nam']);
        $this->model = EquipmentModel::create([
            'code' => 'MOD-TEST',
            'name' => 'Model test',
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    public function test_guest_or_using_dept_user_cannot_access_unassigned_page(): void
    {
        // Guest
        $this->get(route('equipment.unassigned'))
            ->assertRedirect(route('login'));

        // Using dept user
        $usingDeptUser = User::factory()->create([
            'department_id' => $this->targetDept->id,
            'status' => 'active',
        ]);
        $usingDeptUser->assignRole('using_dept');

        $this->actingAs($usingDeptUser)
            ->get(route('equipment.unassigned'))
            ->assertStatus(403);
    }

    public function test_equipment_dept_and_admin_users_can_access_unassigned_page(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('equipment.unassigned'))
            ->assertStatus(200)
            ->assertSee('Gán khoa cho Thiết bị Chưa phân khoa');

        $eqDeptUser = User::factory()->create(['status' => 'active']);
        $eqDeptUser->assignRole('equipment_dept');

        $this->actingAs($eqDeptUser)
            ->get(route('equipment.unassigned'))
            ->assertStatus(200);
    }

    public function test_unassigned_equipment_listing_and_search(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $eq1 = Equipment::create([
            'code' => 'TB-UNASSIGNED-01',
            'name' => 'Máy siêu âm chưa phân khoa 1',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->unassignedDept->id,
        ]);

        $eq2 = Equipment::create([
            'code' => 'TB-ASSIGNED-01',
            'name' => 'Máy đã thuộc Khoa Nội',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->targetDept->id,
        ]);

        Livewire::actingAs($admin)
            ->test(EquipmentUnassigned::class)
            ->assertSee('TB-UNASSIGNED-01')
            ->assertSee('Máy siêu âm chưa phân khoa 1')
            ->assertDontSee('TB-ASSIGNED-01');
    }

    public function test_bulk_department_assignment_and_history_logging(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $eq1 = Equipment::create([
            'code' => 'TB-UN-01',
            'name' => 'Thiết bị 1',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->unassignedDept->id,
        ]);

        $eq2 = Equipment::create([
            'code' => 'TB-UN-02',
            'name' => 'Thiết bị 2',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $this->unassignedDept->id,
        ]);

        Livewire::actingAs($admin)
            ->test(EquipmentUnassigned::class)
            ->set('selectedEquipment', [(string) $eq1->id, (string) $eq2->id])
            ->set('target_department_id', (string) $this->targetDept->id)
            ->call('assignDepartment')
            ->assertHasNoErrors()
            ->assertSee('Đã gán thành công khoa');

        // Check equipment updated to target department
        $this->assertEquals($this->targetDept->id, $eq1->fresh()->department_id);
        $this->assertEquals($this->targetDept->id, $eq2->fresh()->department_id);

        // Check history records created
        $this->assertDatabaseCount('equipment_department_histories', 2);

        $h1 = EquipmentDepartmentHistory::where('equipment_id', $eq1->id)->first();
        $this->assertEquals($this->unassignedDept->id, $h1->from_department_id);
        $this->assertEquals($this->targetDept->id, $h1->to_department_id);
        $this->assertEquals($admin->id, $h1->assigned_by);
    }
}
