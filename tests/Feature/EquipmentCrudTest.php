<?php

namespace Tests\Feature;

use App\Enums\EquipmentConditionEnum;
use App\Livewire\Equipment\EquipmentShow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Database\Seeders\DemoEquipmentSeeder;
use Database\Seeders\DemoMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(DemoMasterDataSeeder::class);
        $this->seed(DemoEquipmentSeeder::class);
    }

    public function test_admin_can_view_create_update_and_soft_delete_equipment(): void
    {
        $admin = User::where('email', 'admin@mems.local')->firstOrFail();

        $response = $this->actingAs($admin)->get('/equipment');
        $response->assertStatus(200);

        $equipment = Equipment::firstOrFail();

        // Admin soft deletes equipment
        Livewire::actingAs($admin)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->call('softDelete')
            ->assertRedirect(route('equipment.index'));

        $this->assertSoftDeleted('equipment', ['id' => $equipment->id]);
    }

    public function test_vattu_can_create_update_and_request_delete_equipment_but_cannot_soft_delete(): void
    {
        $vattu = User::where('email', 'vattu@mems.local')->firstOrFail();

        $response = $this->actingAs($vattu)->get('/equipment');
        $response->assertStatus(200);

        $equipment = Equipment::firstOrFail();

        // Vat tu requests delete
        Livewire::actingAs($vattu)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->call('requestDelete');

        $equipment->refresh();
        $this->assertNotNull($equipment->delete_requested_at);
        $this->assertEquals($vattu->id, $equipment->delete_requested_by);

        // Vat tu attempting softDelete directly throws 403 Forbidden
        Livewire::actingAs($vattu)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->call('softDelete')
            ->assertForbidden();
    }

    public function test_kythuat_can_update_equipment_condition_but_cannot_create_or_delete(): void
    {
        $kythuat = User::where('email', 'kythuat@mems.local')->firstOrFail();

        $equipment = Equipment::firstOrFail();

        Livewire::actingAs($kythuat)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->set('newCondition', EquipmentConditionEnum::CAN_BAO_TRI->value)
            ->set('conditionReason', 'Kỹ thuật viên chuyển trạng thái sang cần bảo trì')
            ->call('updateCondition')
            ->assertHasNoErrors();

        $equipment->refresh();
        $this->assertEquals(EquipmentConditionEnum::CAN_BAO_TRI, $equipment->condition);

        // Kythuat cannot soft delete
        Livewire::actingAs($kythuat)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->call('softDelete')
            ->assertForbidden();
    }

    public function test_kythuat_cannot_access_full_edit_form(): void
    {
        $kythuat = User::where('email', 'kythuat@mems.local')->firstOrFail();
        $equipment = Equipment::firstOrFail();

        $response = $this->actingAs($kythuat)->get('/equipment/'.$equipment->id.'/edit');
        $response->assertStatus(403);
    }

    public function test_khoa_user_can_only_view_and_request_delete_own_department_equipment(): void
    {
        $khoaUser = User::where('email', 'khoa@mems.local')->firstOrFail();
        $khoaDept = $khoaUser->department;

        $ownEquipment = Equipment::where('department_id', $khoaDept->id)->firstOrFail();

        // Khoa user accesses own department equipment -> 200 OK
        $response = $this->actingAs($khoaUser)->get('/equipment/'.$ownEquipment->id);
        $response->assertStatus(200);

        // Khoa user can request delete on own equipment
        Livewire::actingAs($khoaUser)
            ->test(EquipmentShow::class, ['id' => $ownEquipment->id])
            ->call('requestDelete');

        $ownEquipment->refresh();
        $this->assertNotNull($ownEquipment->delete_requested_at);
    }

    public function test_khoa_user_cannot_access_other_department_equipment_by_url(): void
    {
        $khoaUser = User::where('email', 'khoa@mems.local')->firstOrFail();
        $otherDept = Department::where('id', '!=', $khoaUser->department_id)->firstOrFail();

        $otherEquipment = Equipment::where('department_id', $otherDept->id)->firstOrFail();

        // Direct URL access to another department's equipment MUST return 403 Forbidden!
        $response = $this->actingAs($khoaUser)->get('/equipment/'.$otherEquipment->id);
        $response->assertStatus(403);
    }

    public function test_giamdoc_has_read_only_access_without_mutation_actions(): void
    {
        $giamdoc = User::where('email', 'giamdoc@mems.local')->firstOrFail();

        $response = $this->actingAs($giamdoc)->get('/equipment');
        $response->assertStatus(200);

        $equipment = Equipment::firstOrFail();
        $response = $this->actingAs($giamdoc)->get('/equipment/'.$equipment->id);
        $response->assertStatus(200);

        // Cannot create equipment
        $response = $this->actingAs($giamdoc)->get('/equipment/create');
        $response->assertStatus(403);

        // Cannot soft delete
        Livewire::actingAs($giamdoc)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->call('softDelete')
            ->assertForbidden();
    }

    public function test_kythuat_sees_condition_update_form_on_equipment_show(): void
    {
        $kythuat = User::where('email', 'kythuat@mems.local')->firstOrFail();
        $equipment = Equipment::firstOrFail();

        Livewire::actingAs($kythuat)
            ->test(EquipmentShow::class, ['id' => $equipment->id])
            ->assertSee('Cập nhật Tình trạng Kỹ thuật')
            ->assertSee('wire:submit="updateCondition"', false);
    }
}
