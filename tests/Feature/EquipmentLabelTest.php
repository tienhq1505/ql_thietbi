<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatusEnum;
use App\Enums\InventoryItemResultEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentLabelTest extends TestCase
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

        $this->category = EquipmentCategory::create(['name' => 'Máy siêu âm', 'code' => 'MSA-'.uniqid()]);
        $this->manufacturer = Manufacturer::create(['name' => 'GE Healthcare', 'code' => 'GE-'.uniqid()]);
        $this->supplier = Supplier::create(['name' => 'NCC QR', 'code' => 'SUP-'.uniqid()]);
        $this->model = EquipmentModel::create([
            'name' => 'LOGIQ E10',
            'code' => 'MOD-'.uniqid(),
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    protected function createEquipment(Department $dept, string $code, string $status = 'dang_su_dung'): Equipment
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
        ]);
    }

    // ======================================
    // SINGLE EQUIPMENT LABEL TESTS
    // ======================================

    public function test_guest_is_redirected_to_login_for_label(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-GUEST-QR');

        $this->get('/equipment/'.$eq->id.'/label')
            ->assertRedirect('/login');
    }

    public function test_admin_can_print_single_label(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-ADMIN-QR-01');

        $response = $this->actingAs($this->admin)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertSee('TB-ADMIN-QR-01');
        $response->assertSee('<svg', false);
        $response->assertDontSee('<?xml');
    }

    public function test_vattu_can_print_single_label(): void
    {
        $eq = $this->createEquipment($this->kngoaiDept, 'TB-VATTU-QR-01');

        $response = $this->actingAs($this->vattu)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertSee('TB-VATTU-QR-01');
        $response->assertSee('<svg', false);
    }

    public function test_technician_can_print_single_label(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-KT-QR-01');

        $response = $this->actingAs($this->kythuat)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertSee('TB-KT-QR-01');
    }

    public function test_directorate_can_print_single_label(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-BGD-QR-01');

        $response = $this->actingAs($this->giamdoc)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertSee('TB-BGD-QR-01');
        $response->assertSee('<svg', false);
    }

    public function test_using_dept_can_print_own_department_label(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-KHOA-QR-01');

        $response = $this->actingAs($this->khoa)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertSee('TB-KHOA-QR-01');
    }

    public function test_using_dept_forbidden_from_other_department_label(): void
    {
        $eq = $this->createEquipment($this->kngoaiDept, 'TB-NGOAI-QR-01');

        $this->actingAs($this->khoa)
            ->get('/equipment/'.$eq->id.'/label')
            ->assertStatus(403);
    }

    public function test_nonexistent_equipment_returns_404_for_label(): void
    {
        $this->actingAs($this->admin)
            ->get('/equipment/99999/label')
            ->assertStatus(404);
    }

    public function test_label_html_does_not_contain_xml_declaration(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-NOXML-QR');

        $response = $this->actingAs($this->admin)
            ->get('/equipment/'.$eq->id.'/label');

        $response->assertStatus(200);
        $response->assertDontSee('<?xml');
    }

    // ======================================
    // INVENTORY SESSION LABEL TESTS
    // ======================================

    public function test_admin_can_print_labels_by_inventory_session(): void
    {
        $eq1 = $this->createEquipment($this->knoiDept, 'TB-INV-LBL-01');
        $eq2 = $this->createEquipment($this->knoiDept, 'TB-INV-LBL-02');

        $session = InventorySession::create([
            'name' => 'Kỳ in nhãn',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq1->id, 'scanned_code' => $eq1->code, 'result' => null]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq2->id, 'scanned_code' => $eq2->code, 'result' => InventoryItemResultEnum::KHOP]);

        $response = $this->actingAs($this->admin)
            ->get('/inventory/'.$session->id.'/labels');

        $response->assertStatus(200);
        $response->assertSee('TB-INV-LBL-01');
        $response->assertSee('TB-INV-LBL-02');
        $response->assertSee('<svg', false);
        $response->assertDontSee('<?xml');
    }

    public function test_directorate_forbidden_from_inventory_labels(): void
    {
        $session = InventorySession::create([
            'name' => 'Kỳ BGD test',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->giamdoc)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(403);
    }

    public function test_technician_can_print_labels_by_inventory_session(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-KT-INV-LBL');

        $session = InventorySession::create([
            'name' => 'Kỳ KTV test',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq->id, 'scanned_code' => $eq->code, 'result' => null]);

        $this->actingAs($this->kythuat)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(200)
            ->assertSee('TB-KT-INV-LBL');
    }

    public function test_using_dept_can_print_labels_for_own_dept_session(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-KHOA-INV-LBL');

        $session = InventorySession::create([
            'name' => 'Kỳ Khoa Nội',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq->id, 'scanned_code' => $eq->code, 'result' => null]);

        $this->actingAs($this->khoa)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(200)
            ->assertSee('TB-KHOA-INV-LBL');
    }

    public function test_using_dept_forbidden_from_other_dept_session_labels(): void
    {
        $session = InventorySession::create([
            'name' => 'Kỳ Khoa Ngoại',
            'department_id' => $this->kngoaiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->khoa)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(403);
    }

    public function test_using_dept_forbidden_from_all_hospital_session_labels(): void
    {
        $session = InventorySession::create([
            'name' => 'Kỳ toàn viện',
            'department_id' => null,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->khoa)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(403);
    }

    public function test_session_labels_exclude_da_thanh_ly_and_null_equipment(): void
    {
        $eqActive = $this->createEquipment($this->knoiDept, 'TB-ACTIVE-LBL');
        $eqDisposed = $this->createEquipment($this->knoiDept, 'TB-DISPOSED-LBL', EquipmentStatusEnum::DA_THANH_LY->value);

        $session = InventorySession::create([
            'name' => 'Kỳ filter test',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eqActive->id, 'scanned_code' => $eqActive->code, 'result' => null]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eqDisposed->id, 'scanned_code' => $eqDisposed->code, 'result' => null]);
        // Item with null equipment_id (orphan)
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => null, 'scanned_code' => 'ORPHAN-CODE', 'result' => InventoryItemResultEnum::THUA]);

        $response = $this->actingAs($this->admin)
            ->get('/inventory/'.$session->id.'/labels');

        $response->assertStatus(200);
        $response->assertSee('TB-ACTIVE-LBL');
        $response->assertDontSee('TB-DISPOSED-LBL');
        $response->assertDontSee('ORPHAN-CODE');
    }

    public function test_session_labels_using_dept_cannot_see_excess_items_from_other_dept(): void
    {
        $eqOwn = $this->createEquipment($this->knoiDept, 'TB-OWN-DEPT-LBL');
        $eqOther = $this->createEquipment($this->kngoaiDept, 'TB-OTHER-DEPT-LBL');

        $session = InventorySession::create([
            'name' => 'Kỳ Khoa Nội with excess',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->khoa->id,
        ]);

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eqOwn->id, 'scanned_code' => $eqOwn->code, 'result' => InventoryItemResultEnum::KHOP]);
        // Excess item from other department
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eqOther->id, 'scanned_code' => $eqOther->code, 'result' => InventoryItemResultEnum::THUA]);

        $response = $this->actingAs($this->khoa)
            ->get('/inventory/'.$session->id.'/labels');

        $response->assertStatus(200);
        $response->assertSee('TB-OWN-DEPT-LBL');
        // The other department's excess equipment name and code must NOT appear
        $response->assertDontSee('TB-OTHER-DEPT-LBL');
        $response->assertDontSee('Thiết bị TB-OTHER-DEPT-LBL');
    }

    public function test_nonexistent_session_returns_404_for_labels(): void
    {
        $this->actingAs($this->admin)
            ->get('/inventory/99999/labels')
            ->assertStatus(404);
    }

    // ======================================
    // DEPARTMENT LABEL TESTS
    // ======================================

    public function test_admin_can_print_labels_by_department(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-DEPT-LBL-01');

        $response = $this->actingAs($this->admin)
            ->get('/departments/'.$this->knoiDept->id.'/equipment-labels');

        $response->assertStatus(200);
        $response->assertSee('TB-DEPT-LBL-01');
        $response->assertSee('<svg', false);
    }

    public function test_using_dept_can_print_own_department_labels(): void
    {
        $eq = $this->createEquipment($this->knoiDept, 'TB-OWNDEPT-LBL');

        $response = $this->actingAs($this->khoa)
            ->get('/departments/'.$this->knoiDept->id.'/equipment-labels');

        $response->assertStatus(200);
        $response->assertSee('TB-OWNDEPT-LBL');
    }

    public function test_using_dept_forbidden_from_other_department_labels(): void
    {
        $this->actingAs($this->khoa)
            ->get('/departments/'.$this->kngoaiDept->id.'/equipment-labels')
            ->assertStatus(403);
    }

    public function test_department_labels_exclude_da_thanh_ly(): void
    {
        $eqActive = $this->createEquipment($this->knoiDept, 'TB-DEPT-ACTIVE');
        $eqDisposed = $this->createEquipment($this->knoiDept, 'TB-DEPT-DISPOSED', EquipmentStatusEnum::DA_THANH_LY->value);

        $response = $this->actingAs($this->admin)
            ->get('/departments/'.$this->knoiDept->id.'/equipment-labels');

        $response->assertStatus(200);
        $response->assertSee('TB-DEPT-ACTIVE');
        $response->assertDontSee('TB-DEPT-DISPOSED');
    }

    // ======================================
    // USING_DEPT WITHOUT DEPARTMENT_ID
    // ======================================

    public function test_using_dept_without_department_id_forbidden_from_all_label_routes(): void
    {
        $userNoDept = User::create([
            'name' => 'Khoa Chưa Gán Label',
            'email' => 'khoa_nodept_lbl_'.uniqid().'@mems.local',
            'password' => bcrypt('password'),
            'department_id' => null,
            'status' => 'active',
        ]);
        $userNoDept->assignRole('using_dept');

        $eq = $this->createEquipment($this->knoiDept, 'TB-NODEPT-LBL');

        // Single label -> 403 (EquipmentPolicy::view checks department_id)
        $this->actingAs($userNoDept)
            ->get('/equipment/'.$eq->id.'/label')
            ->assertStatus(403);

        // Session labels -> 403
        $session = InventorySession::create([
            'name' => 'Kỳ no dept test',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($userNoDept)
            ->get('/inventory/'.$session->id.'/labels')
            ->assertStatus(403);

        // Department labels -> 403
        $this->actingAs($userNoDept)
            ->get('/departments/'.$this->knoiDept->id.'/equipment-labels')
            ->assertStatus(403);
    }
}
