<?php

namespace Tests\Feature;

use App\Livewire\MasterDataIndex;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_without_permission_cannot_access_master_data_page(): void
    {
        $usingDeptUser = User::factory()->create(['status' => 'active']);
        $usingDeptUser->assignRole('using_dept');

        $this->actingAs($usingDeptUser)
            ->get(route('master-data.index'))
            ->assertStatus(403);
    }

    public function test_user_with_master_data_manage_permission_can_access(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('master-data.index'))
            ->assertStatus(200)
            ->assertSee('Quản lý Danh mục Hệ thống');
    }

    public function test_create_and_edit_category(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(MasterDataIndex::class)
            ->set('activeTab', 'categories')
            ->set('code', 'CAT-NEW')
            ->set('name', 'Loại mới test')
            ->set('description', 'Mô tả test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('equipment_categories', [
            'code' => 'CAT-NEW',
            'name' => 'Loại mới test',
        ]);
    }

    public function test_delete_category_is_blocked_when_referenced(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $cat = EquipmentCategory::create(['code' => 'CAT-REF', 'name' => 'Loại có tham chiếu']);
        $mfg = Manufacturer::create(['code' => 'MFG-REF', 'name' => 'Hãng test']);
        EquipmentModel::create([
            'code' => 'MOD-REF',
            'name' => 'Model tham chiếu',
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
        ]);

        Livewire::actingAs($admin)
            ->test(MasterDataIndex::class)
            ->set('activeTab', 'categories')
            ->call('delete', $cat->id)
            ->assertSee('Không thể xóa loại thiết bị này');

        // Check category is NOT soft-deleted
        $this->assertNull($cat->fresh()->deleted_at);
    }
}
