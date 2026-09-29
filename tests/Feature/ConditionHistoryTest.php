<?php

namespace Tests\Feature;

use App\Enums\EquipmentConditionEnum;
use App\Livewire\Equipment\EquipmentShow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentConditionHistory;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConditionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $techUser;

    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $department = Department::create([
            'code' => 'K-TEST',
            'name' => 'Khoa Test',
        ]);

        $category = EquipmentCategory::create(['code' => 'CAT-TEST', 'name' => 'Loại Test']);
        $manufacturer = Manufacturer::create(['code' => 'MFG-TEST', 'name' => 'Hãng Test']);

        $model = EquipmentModel::create([
            'code' => 'MOD-TEST',
            'name' => 'Model Test',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);

        $this->adminUser = User::factory()->create(['department_id' => $department->id]);
        $this->adminUser->assignRole('admin');

        $this->techUser = User::factory()->create(['department_id' => $department->id]);
        $this->techUser->assignRole('technician');

        $this->equipment = Equipment::create([
            'code' => 'TB-HIST-001',
            'name' => 'Thiết bị test lịch sử',
            'equipment_model_id' => $model->id,
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'department_id' => $department->id,
            'serial_number' => 'SN-HIST-001',
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
        ]);
    }

    public function test_updating_condition_creates_history_record(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->set('newCondition', EquipmentConditionEnum::CAN_BAO_TRI->value)
            ->set('conditionReason', 'Phát hiện tiếng ồn lạ khi vận hành')
            ->set('conditionNote', 'Cần kiểm tra động cơ vào ngày mai')
            ->call('updateCondition')
            ->assertHasNoErrors()
            ->assertSee('Đã cập nhật tình trạng kỹ thuật và lưu lịch sử thành công.');

        $this->equipment->refresh();
        $this->assertEquals(EquipmentConditionEnum::CAN_BAO_TRI, $this->equipment->condition);

        $this->assertDatabaseCount('equipment_condition_history', 1);

        $history = EquipmentConditionHistory::first();
        $this->assertEquals($this->equipment->id, $history->equipment_id);
        $this->assertEquals(EquipmentConditionEnum::DANG_HOAT_DONG, $history->old_condition);
        $this->assertEquals(EquipmentConditionEnum::CAN_BAO_TRI, $history->new_condition);
        $this->assertEquals('Phát hiện tiếng ồn lạ khi vận hành', $history->reason);
        $this->assertEquals('Cần kiểm tra động cơ vào ngày mai', $history->note);
        $this->assertEquals($this->adminUser->id, $history->checked_by);
    }

    public function test_missing_reason_fails_validation_and_does_not_create_history(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->set('newCondition', EquipmentConditionEnum::CAN_BAO_TRI->value)
            ->set('conditionReason', '')
            ->call('updateCondition')
            ->assertHasErrors(['conditionReason']);

        $this->equipment->refresh();
        $this->assertEquals(EquipmentConditionEnum::DANG_HOAT_DONG, $this->equipment->condition);
        $this->assertDatabaseCount('equipment_condition_history', 0);
    }

    public function test_selecting_same_condition_fails_and_does_not_create_history(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->set('newCondition', EquipmentConditionEnum::DANG_HOAT_DONG->value)
            ->set('conditionReason', 'Lý do giữ nguyên')
            ->call('updateCondition')
            ->assertHasErrors(['newCondition']);

        $this->assertDatabaseCount('equipment_condition_history', 0);
    }

    public function test_updating_three_times_creates_three_history_records_latest_first(): void
    {
        $show = Livewire::actingAs($this->adminUser)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id]);

        // Update 1: DANG_HOAT_DONG -> CAN_THEO_DOI
        $show->set('newCondition', EquipmentConditionEnum::CAN_THEO_DOI->value)
            ->set('conditionReason', 'Lần 1: Cần theo dõi thêm')
            ->call('updateCondition')
            ->assertHasNoErrors();

        // Update 2: CAN_THEO_DOI -> CAN_BAO_TRI
        $show->set('newCondition', EquipmentConditionEnum::CAN_BAO_TRI->value)
            ->set('conditionReason', 'Lần 2: Chuyển sang bảo trì')
            ->call('updateCondition')
            ->assertHasNoErrors();

        // Update 3: CAN_BAO_TRI -> DANG_HOAT_DONG
        $show->set('newCondition', EquipmentConditionEnum::DANG_HOAT_DONG->value)
            ->set('conditionReason', 'Lần 3: Đã sửa xong và hoạt động lại')
            ->call('updateCondition')
            ->assertHasNoErrors();

        $this->equipment->refresh();
        $histories = $this->equipment->conditionHistories;

        $this->assertCount(3, $histories);
        $this->assertEquals('Lần 3: Đã sửa xong và hoạt động lại', $histories->first()->reason);
        $this->assertEquals(EquipmentConditionEnum::CAN_BAO_TRI, $histories->first()->old_condition);
        $this->assertEquals(EquipmentConditionEnum::DANG_HOAT_DONG, $histories->first()->new_condition);
    }

    public function test_technician_can_update_condition_with_reason(): void
    {
        Livewire::actingAs($this->techUser)
            ->test(EquipmentShow::class, ['id' => $this->equipment->id])
            ->set('newCondition', EquipmentConditionEnum::HONG->value)
            ->set('conditionReason', 'Kỹ thuật viên ghi nhận máy bị hỏng phần nguồn')
            ->call('updateCondition')
            ->assertHasNoErrors();

        $this->equipment->refresh();
        $this->assertEquals(EquipmentConditionEnum::HONG, $this->equipment->condition);
        $this->assertDatabaseCount('equipment_condition_history', 1);

        $history = EquipmentConditionHistory::first();
        $this->assertEquals($this->techUser->id, $history->checked_by);
    }
}
