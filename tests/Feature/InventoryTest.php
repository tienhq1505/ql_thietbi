<?php

namespace Tests\Feature;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\InventoryItemResultEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\InventoryShow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentConditionHistory;
use App\Models\EquipmentModel;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $vattu;

    protected User $kythuat;

    protected User $khoa;

    protected User $giamdoc;

    protected Department $knoiDept;

    protected Department $kngoaiDept;

    protected EquipmentCategory $category;

    protected Manufacturer $manufacturer;

    protected EquipmentModel $model;

    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@mems.local')->firstOrFail();
        $this->vattu = User::where('email', 'vattu@mems.local')->firstOrFail();
        $this->kythuat = User::where('email', 'kythuat@mems.local')->firstOrFail();
        $this->khoa = User::where('email', 'khoa@mems.local')->firstOrFail();
        $this->giamdoc = User::where('email', 'giamdoc@mems.local')->firstOrFail();

        $this->knoiDept = Department::where('code', 'KNOI')->firstOrFail();
        $this->kngoaiDept = Department::where('code', 'KNOAI3A')->firstOrFail();

        $this->category = EquipmentCategory::create(['name' => 'Máy thở', 'code' => 'MT-'.uniqid()]);
        $this->manufacturer = Manufacturer::create(['name' => 'Drager', 'code' => 'DRA-'.uniqid()]);
        $this->supplier = Supplier::create(['name' => 'NCC Med', 'code' => 'SUP-'.uniqid()]);
        $this->model = EquipmentModel::create([
            'name' => 'Evita V300',
            'code' => 'MOD-'.uniqid(),
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    protected function createEquipment(Department $dept, string $code, string $status = 'dang_su_dung', string $condition = 'dang_hoat_dong'): Equipment
    {
        $loc = Location::create([
            'name' => 'Phòng '.$dept->code,
            'code' => 'LOC-'.uniqid(),
            'department_id' => $dept->id,
        ]);

        return Equipment::create([
            'name' => 'Thiết bị '.$code,
            'code' => $code,
            'serial_number' => 'SN-'.uniqid(),
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'equipment_model_id' => $this->model->id,
            'department_id' => $dept->id,
            'location_id' => $loc->id,
            'supplier_id' => $this->supplier->id,
            'status' => $status,
            'condition' => $condition,
        ]);
    }

    public function test_directorate_is_forbidden_from_inventory(): void
    {
        $this->actingAs($this->giamdoc)
            ->get('/inventory')
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/inventory')->assertRedirect('/login');
    }

    public function test_technician_cannot_create_or_complete_session(): void
    {
        // Technician can view index
        $this->actingAs($this->kythuat)
            ->get('/inventory')
            ->assertStatus(200);

        // Cannot create
        Livewire::actingAs($this->kythuat)
            ->test(InventoryIndex::class)
            ->call('openCreateModal')
            ->assertStatus(403);

        Livewire::actingAs($this->kythuat)
            ->test(InventoryIndex::class)
            ->call('createSession')
            ->assertStatus(403);

        // Create a session by admin
        $session = InventorySession::create([
            'name' => 'Kỳ test tech',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        // Technician cannot complete session
        Livewire::actingAs($this->kythuat)
            ->test(InventoryShow::class, ['session' => $session])
            ->call('completeSession')
            ->assertStatus(403);
    }

    public function test_using_dept_can_only_access_own_department_session(): void
    {
        // Session of Khoa Ngoại
        $kngoaiSession = InventorySession::create([
            'name' => 'Kỳ Khoa Ngoại',
            'department_id' => $this->kngoaiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        // Khoa Nội user tries to access Khoa Ngoại session -> 403
        $this->actingAs($this->khoa)
            ->get('/inventory/'.$kngoaiSession->id)
            ->assertStatus(403);

        // Khoa Nội session -> 200
        $knoiSession = InventorySession::create([
            'name' => 'Kỳ Khoa Nội',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        $this->actingAs($this->khoa)
            ->get('/inventory/'.$knoiSession->id)
            ->assertStatus(200);
    }

    public function test_create_session_snapshots_items_and_excludes_da_thanh_ly(): void
    {
        // 2 active equipments, 1 da_thanh_ly
        $eq1 = $this->createEquipment($this->knoiDept, 'TB-ACTIVE-01', EquipmentStatusEnum::DANG_SU_DUNG->value);
        $eq2 = $this->createEquipment($this->knoiDept, 'TB-ACTIVE-02', EquipmentStatusEnum::DU_PHONG->value);
        $eq3 = $this->createEquipment($this->knoiDept, 'TB-DISPOSED-03', EquipmentStatusEnum::DA_THANH_LY->value);

        Livewire::actingAs($this->admin)
            ->test(InventoryIndex::class)
            ->set('name', 'Kỳ snapshot test')
            ->set('department_id', $this->knoiDept->id)
            ->set('start_date', now()->format('Y-m-d'))
            ->set('end_date', now()->addDays(3)->format('Y-m-d'))
            ->call('createSession');

        $session = InventorySession::where('name', 'Kỳ snapshot test')->firstOrFail();
        $this->assertEquals(2, $session->items()->count());

        $this->assertTrue($session->items()->where('equipment_id', $eq1->id)->exists());
        $this->assertTrue($session->items()->where('equipment_id', $eq2->id)->exists());
        $this->assertFalse($session->items()->where('equipment_id', $eq3->id)->exists());
    }

    public function test_scan_matched_equipment(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-MATCH-01');

        $session = InventorySession::create([
            'name' => 'Kỳ quét khớp',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $item = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'system_department_id' => $eq->department_id,
            'system_location_id' => $eq->location_id,
            'result' => null,
        ]);

        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'TB-MATCH-01')
            ->call('submitManualCode')
            ->assertSee('Đã xác nhận KHỚP thiết bị');

        $item->refresh();
        $this->assertEquals(InventoryItemResultEnum::KHOP, $item->result);
        $this->assertEquals($this->admin->id, $item->checked_by);
        $this->assertNotNull($item->checked_at);
    }

    public function test_scan_duplicate_equipment_shows_warning_and_does_not_create_duplicate_row(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-DUP-01');

        $session = InventorySession::create([
            'name' => 'Kỳ quét trùng',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'system_department_id' => $eq->department_id,
            'system_location_id' => $eq->location_id,
            'result' => InventoryItemResultEnum::KHOP,
            'checked_by' => $this->admin->id,
            'checked_at' => now(),
        ]);

        $this->assertEquals(1, $session->items()->count());

        // Scan again
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'TB-DUP-01')
            ->call('submitManualCode')
            ->assertSee('đã được kiểm trước đó');

        $this->assertEquals(1, $session->items()->count());
    }

    public function test_scan_excess_equipment_creates_item_thua(): void
    {
        // Equipment belongs to Khoa Ngoại
        $eqNgoai = $this->createEquipment($this->kngoaiDept, 'TB-EXCESS-01');

        // Session of all hospital or checked by admin
        $session = InventorySession::create([
            'name' => 'Kỳ quét thừa',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(0, $session->items()->count());

        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'TB-EXCESS-01')
            ->call('submitManualCode')
            ->assertSee('Đã ghi nhận thiết bị THỪA');

        $this->assertEquals(1, $session->items()->count());
        $excessItem = $session->items()->first();
        $this->assertEquals(InventoryItemResultEnum::THUA, $excessItem->result);
        $this->assertEquals($eqNgoai->id, $excessItem->equipment_id);
    }

    public function test_scan_non_existent_code_shows_error_and_creates_nothing(): void
    {
        $session = InventorySession::create([
            'name' => 'Kỳ quét sai mã',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'NON-EXISTENT-CODE')
            ->call('submitManualCode')
            ->assertSee('không tồn tại trong hệ thống');

        $this->assertEquals(0, $session->items()->count());
    }

    public function test_using_dept_scanning_other_department_equipment_shows_scope_error_without_leaking_info(): void
    {
        // Equipment of Khoa Ngoại
        $eqNgoai = $this->createEquipment($this->kngoaiDept, 'TB-NGOAI-999');

        // Khoa Nội session
        $session = InventorySession::create([
            'name' => 'Kỳ Khoa Nội',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        Livewire::actingAs($this->khoa)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'TB-NGOAI-999')
            ->call('submitManualCode')
            ->assertSee('Mã không thuộc phạm vi kiểm kê của bạn.');

        $this->assertEquals(0, $session->items()->count());
    }

    public function test_mark_broken_by_authorized_user_updates_equipment_and_records_condition_history(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-BROKEN-01', 'dang_su_dung', EquipmentConditionEnum::DANG_HOAT_DONG->value);

        $session = InventorySession::create([
            'name' => 'Kỳ báo hỏng',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $item = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'result' => null,
        ]);

        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('selectedItemIdForBroken', $item->id)
            ->set('brokenReason', 'Hư hỏng bo mạch chính')
            ->call('confirmMarkBroken')
            ->assertSee('Đã đánh dấu HỎNG thiết bị');

        $item->refresh();
        $this->assertEquals(InventoryItemResultEnum::HONG, $item->result);

        $eq->refresh();
        $this->assertEquals(EquipmentConditionEnum::HONG, $eq->condition);

        $history = EquipmentConditionHistory::where('equipment_id', $eq->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals(EquipmentConditionEnum::HONG, $history->new_condition);
        $this->assertEquals('Hư hỏng bo mạch chính', $history->reason);
    }

    public function test_mark_broken_by_using_dept_does_not_change_equipment_condition_or_record_history(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-BROKEN-KHOA-01', 'dang_su_dung', EquipmentConditionEnum::DANG_HOAT_DONG->value);

        $session = InventorySession::create([
            'name' => 'Kỳ Khoa Nội',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        $item = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'result' => null,
        ]);

        $historyCountBefore = EquipmentConditionHistory::where('equipment_id', $eq->id)->count();

        // Khoa user marks broken
        Livewire::actingAs($this->khoa)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('selectedItemIdForBroken', $item->id)
            ->set('brokenReason', 'Khoa phát hiện hỏng màn hình')
            ->call('confirmMarkBroken');

        $item->refresh();
        $this->assertEquals(InventoryItemResultEnum::HONG, $item->result);

        $eq->refresh();
        // Equipment condition MUST NOT change
        $this->assertEquals(EquipmentConditionEnum::DANG_HOAT_DONG, $eq->condition);

        // No new history record
        $this->assertEquals($historyCountBefore, EquipmentConditionHistory::where('equipment_id', $eq->id)->count());
    }

    public function test_mark_broken_on_already_broken_equipment_does_not_create_duplicate_history(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-ALREADY-BROKEN', 'dang_su_dung', EquipmentConditionEnum::HONG->value);

        $session = InventorySession::create([
            'name' => 'Kỳ máy đã hỏng',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $item = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'result' => null,
        ]);

        $historyCountBefore = EquipmentConditionHistory::where('equipment_id', $eq->id)->count();

        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('selectedItemIdForBroken', $item->id)
            ->set('brokenReason', 'Vẫn hỏng như cũ')
            ->call('confirmMarkBroken');

        $item->refresh();
        $this->assertEquals(InventoryItemResultEnum::HONG, $item->result);

        // No new history created because it was already HONG
        $this->assertEquals($historyCountBefore, EquipmentConditionHistory::where('equipment_id', $eq->id)->count());
    }

    public function test_complete_session_marks_untested_as_missing_and_locks_session_server_side(): void
    {
        $eq1 = $this->createEquipment($this->knoiDept, 'TB-COMP-01');
        $eq2 = $this->createEquipment($this->knoiDept, 'TB-COMP-02');

        $session = InventorySession::create([
            'name' => 'Kỳ chốt kiểm kê',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $item1 = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq1->id,
            'scanned_code' => $eq1->code,
            'result' => InventoryItemResultEnum::KHOP,
        ]);

        $item2 = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq2->id,
            'scanned_code' => $eq2->code,
            'result' => null, // untested
        ]);

        // Complete session
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->call('completeSession');

        $session->refresh();
        $this->assertTrue($session->isCompleted());
        $this->assertNotNull($session->completed_at);

        $item1->refresh();
        $this->assertEquals(InventoryItemResultEnum::KHOP, $item1->result);

        $item2->refresh();
        $this->assertEquals(InventoryItemResultEnum::THIEU, $item2->result);

        // Attempt to scan/modify on completed session must be forbidden server-side (403)
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('manualCode', 'TB-COMP-01')
            ->call('submitManualCode')
            ->assertStatus(403);
    }

    public function test_cannot_mark_broken_for_item_belonging_to_another_session_or_department(): void
    {
        $eqNoi = $this->createEquipment($this->knoiDept, 'TB-NOI-SESSION-A');
        $eqNgoai = $this->createEquipment($this->kngoaiDept, 'TB-NGOAI-SESSION-B');

        $sessionA = InventorySession::create([
            'name' => 'Kỳ Khoa Nội A',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        $itemA = InventoryItem::create([
            'inventory_session_id' => $sessionA->id,
            'equipment_id' => $eqNoi->id,
            'scanned_code' => $eqNoi->code,
            'result' => null,
        ]);

        $sessionB = InventorySession::create([
            'name' => 'Kỳ Khoa Ngoại B',
            'department_id' => $this->kngoaiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $itemB = InventoryItem::create([
            'inventory_session_id' => $sessionB->id,
            'equipment_id' => $eqNgoai->id,
            'scanned_code' => $eqNgoai->code,
            'result' => null,
        ]);

        // User Khoa Nội attempts to open modal with itemB (from Session B) -> ModelNotFoundException
        try {
            Livewire::actingAs($this->khoa)
                ->test(InventoryShow::class, ['session' => $sessionA])
                ->call('openBrokenModal', $itemB->id);
            $this->fail('Dự kiến phải ném ModelNotFoundException khi truy cập item của kỳ khác.');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }

        // User Khoa Nội directly sets selectedItemIdForBroken = itemB->id and calls confirmMarkBroken -> ModelNotFoundException
        try {
            Livewire::actingAs($this->khoa)
                ->test(InventoryShow::class, ['session' => $sessionA])
                ->set('selectedItemIdForBroken', $itemB->id)
                ->set('brokenReason', 'Cố tình đánh dấu hỏng kỳ khác')
                ->call('confirmMarkBroken');
            $this->fail('Dự kiến phải ném ModelNotFoundException khi xác nhận item của kỳ khác.');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }

        // Verify itemB was NOT modified
        $itemB->refresh();
        $this->assertNull($itemB->result);
    }

    public function test_cannot_mark_broken_on_completed_session(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-LOCK-BROKEN');

        $session = InventorySession::create([
            'name' => 'Kỳ đã chốt không cho báo hỏng',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::COMPLETED,
            'completed_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $item = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'result' => InventoryItemResultEnum::KHOP,
        ]);

        // openBrokenModal on completed session -> 403
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->call('openBrokenModal', $item->id)
            ->assertStatus(403);

        // confirmMarkBroken on completed session -> 403
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->set('selectedItemIdForBroken', $item->id)
            ->set('brokenReason', 'Thử đổi kỳ đã chốt')
            ->call('confirmMarkBroken')
            ->assertStatus(403);

        $item->refresh();
        $this->assertEquals(InventoryItemResultEnum::KHOP, $item->result);
    }

    public function test_using_dept_without_department_id_is_forbidden_and_cannot_create_session(): void
    {
        $userNoDept = User::create([
            'name' => 'Khoa Chưa Gán',
            'email' => 'khoa_nodept_'.uniqid().'@mems.local',
            'password' => bcrypt('password'),
            'department_id' => null,
            'status' => 'active',
        ]);
        $userNoDept->assignRole('using_dept');

        // HTTP access to /inventory -> 403
        $this->actingAs($userNoDept)
            ->get('/inventory')
            ->assertStatus(403);

        // Livewire InventoryIndex mount -> 403
        Livewire::actingAs($userNoDept)
            ->test(InventoryIndex::class)
            ->assertStatus(403);

        // Session for all hospital (department_id = null)
        $allHospitalSession = InventorySession::create([
            'name' => 'Kỳ toàn viện',
            'department_id' => null,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        // using_dept with NO department accessing session -> 403
        $this->actingAs($userNoDept)
            ->get('/inventory/'.$allHospitalSession->id)
            ->assertStatus(403);

        // using_dept WITH department accessing all-hospital session (department_id = null) -> 403 (null considered invalid/out of scope)
        $this->actingAs($this->khoa)
            ->get('/inventory/'.$allHospitalSession->id)
            ->assertStatus(403);
    }

    public function test_inventory_index_create_session_uses_relationship_count_for_message(): void
    {
        $this->createEquipment($this->knoiDept, 'TB-MSG-01');
        $this->createEquipment($this->knoiDept, 'TB-MSG-02');

        Livewire::actingAs($this->admin)
            ->test(InventoryIndex::class)
            ->set('name', 'Kỳ đếm thiết bị')
            ->set('department_id', $this->knoiDept->id)
            ->set('start_date', now()->format('Y-m-d'))
            ->set('end_date', now()->addDays(3)->format('Y-m-d'))
            ->call('createSession')
            ->assertSessionHas('message', 'Đã khởi tạo kỳ kiểm kê "Kỳ đếm thiết bị" với 2 thiết bị thành công.');
    }
}
