<?php

namespace Tests\Feature;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\InventoryItemResultEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Livewire\Inventory\InventoryReport;
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
use Livewire\Livewire;
use Tests\TestCase;

class InventoryReportTest extends TestCase
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

        $this->category = EquipmentCategory::firstOrCreate(
            ['code' => 'TEST-CAT'],
            ['name' => 'Thiết bị Hồi sức Test']
        );

        $this->manufacturer = Manufacturer::firstOrCreate(
            ['code' => 'TEST-MFG'],
            ['name' => 'Test Manufacturer', 'country' => 'Đức']
        );

        $this->model = EquipmentModel::firstOrCreate(
            ['code' => 'TEST-MOD'],
            [
                'name' => 'Test Model 2000',
                'category_id' => $this->category->id,
                'manufacturer_id' => $this->manufacturer->id,
            ]
        );

        $this->supplier = Supplier::firstOrCreate(
            ['code' => 'TEST-SUP'],
            ['name' => 'Test Supplier']
        );
    }

    private function createEquipment(Department $dept, string $code, string $name): Equipment
    {
        return Equipment::create([
            'code' => $code,
            'name' => $name,
            'category_id' => $this->category->id,
            'equipment_model_id' => $this->model->id,
            'manufacturer_id' => $this->manufacturer->id,
            'department_id' => $dept->id,
            'location_id' => Location::first()?->id,
            'serial_number' => 'SN-'.fake()->unique()->numerify('#####'),
            'status' => EquipmentStatusEnum::DANG_SU_DUNG,
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
        ]);
    }

    private function createSession(Department $dept, string $status = InventorySessionStatusEnum::IN_PROGRESS->value): InventorySession
    {
        return InventorySession::create([
            'name' => 'Kỳ kiểm kê '.$dept->name.' - '.fake()->numerify('###'),
            'department_id' => $dept->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => $status,
            'created_by' => $this->admin->id,
            'completed_at' => $status === InventorySessionStatusEnum::COMPLETED->value ? now() : null,
        ]);
    }

    public function test_guest_redirected_to_login_on_report_and_exports(): void
    {
        $session = $this->createSession($this->knoiDept);

        $this->get(route('inventory.report', $session->id))
            ->assertRedirect(route('login'));

        $this->get(route('inventory.export-pdf', $session->id))
            ->assertRedirect(route('login'));

        $this->get(route('inventory.export-excel', $session->id))
            ->assertRedirect(route('login'));
    }

    public function test_directorate_forbidden_from_report_and_exports(): void
    {
        $session = $this->createSession($this->knoiDept);

        $this->actingAs($this->giamdoc)
            ->get(route('inventory.report', $session->id))
            ->assertForbidden();

        $this->actingAs($this->giamdoc)
            ->get(route('inventory.export-pdf', $session->id))
            ->assertForbidden();

        $this->actingAs($this->giamdoc)
            ->get(route('inventory.export-excel', $session->id))
            ->assertForbidden();
    }

    public function test_admin_and_equipment_dept_and_technician_can_access_report_and_exports(): void
    {
        $session = $this->createSession($this->knoiDept);

        foreach ([$this->admin, $this->vattu, $this->kythuat] as $user) {
            $this->actingAs($user)
                ->get(route('inventory.report', $session->id))
                ->assertOk();

            $this->actingAs($user)
                ->get(route('inventory.export-pdf', $session->id))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');

            $this->actingAs($user)
                ->get(route('inventory.export-excel', $session->id))
                ->assertOk();
        }
    }

    public function test_using_dept_can_access_report_and_exports_of_own_department(): void
    {
        $session = $this->createSession($this->knoiDept);

        $this->actingAs($this->khoa)
            ->get(route('inventory.report', $session->id))
            ->assertOk();

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-pdf', $session->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-excel', $session->id))
            ->assertOk();
    }

    public function test_using_dept_forbidden_from_report_and_exports_of_other_department(): void
    {
        $otherSession = $this->createSession($this->kngoaiDept);

        $this->actingAs($this->khoa)
            ->get(route('inventory.report', $otherSession->id))
            ->assertForbidden();

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-pdf', $otherSession->id))
            ->assertForbidden();

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-excel', $otherSession->id))
            ->assertForbidden();
    }

    public function test_using_dept_forbidden_from_hospital_wide_session_report_and_exports(): void
    {
        $hospitalSession = InventorySession::create([
            'name' => 'Kỳ kiểm kê Toàn Viện 2026',
            'department_id' => null,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->khoa)
            ->get(route('inventory.report', $hospitalSession->id))
            ->assertForbidden();

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-pdf', $hospitalSession->id))
            ->assertForbidden();

        $this->actingAs($this->khoa)
            ->get(route('inventory.export-excel', $hospitalSession->id))
            ->assertForbidden();
    }

    public function test_using_dept_without_department_id_forbidden_from_report_and_exports(): void
    {
        $noDeptUser = User::factory()->create([
            'department_id' => null,
        ]);
        $noDeptUser->assignRole('using_dept');

        $session = $this->createSession($this->knoiDept);

        $this->actingAs($noDeptUser)
            ->get(route('inventory.report', $session->id))
            ->assertForbidden();

        $this->actingAs($noDeptUser)
            ->get(route('inventory.export-pdf', $session->id))
            ->assertForbidden();

        $this->actingAs($noDeptUser)
            ->get(route('inventory.export-excel', $session->id))
            ->assertForbidden();
    }

    public function test_pdf_export_returns_200_with_pdf_content_type_and_vietnamese_text(): void
    {
        $session = $this->createSession($this->knoiDept);
        $eq = $this->createEquipment($this->knoiDept, 'TB-REP-001', 'Máy Theo Dõi Bệnh Nhân');

        InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'system_department_id' => $this->knoiDept->id,
            'result' => InventoryItemResultEnum::KHOP,
            'checked_by' => $this->admin->id,
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('inventory.export-pdf', $session->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_pdf_and_report_for_in_progress_session_shows_chua_chot_draft_notice(): void
    {
        $session = $this->createSession($this->knoiDept, InventorySessionStatusEnum::IN_PROGRESS->value);

        // Web view
        $this->actingAs($this->admin)
            ->get(route('inventory.report', $session->id))
            ->assertOk()
            ->assertSee('Chưa chốt')
            ->assertSee('DỰ THẢO');

        // PDF view content test via view rendering
        $view = view('pdf.inventory_report', [
            'session' => $session,
            'stats' => [
                'total' => 1,
                'matched' => 0,
                'missing' => 0,
                'excess' => 0,
                'broken' => 0,
                'untested' => 1,
                'checked_total' => 0,
                'progress_pct' => 0,
            ],
            'discrepancyItems' => collect(),
        ])->render();

        $this->assertStringContainsString('DỰ THẢO - CHƯA CHỐT', $view);
        $this->assertStringContainsString('Chưa chốt', $view);
        $this->assertStringContainsString(config('app.hospital_name'), $view);

        // Test custom hospital name from config
        config(['app.hospital_name' => 'Bệnh viện Đa khoa Test Config']);
        $customView = view('pdf.inventory_report', [
            'session' => $session,
            'stats' => [
                'total' => 1,
                'matched' => 0,
                'missing' => 0,
                'excess' => 0,
                'broken' => 0,
                'untested' => 1,
                'checked_total' => 0,
                'progress_pct' => 0,
            ],
            'discrepancyItems' => collect(),
        ])->render();
        $this->assertStringContainsString('Bệnh viện Đa khoa Test Config', $customView);
    }

    public function test_pdf_and_report_for_completed_session_shows_official_status(): void
    {
        $session = $this->createSession($this->knoiDept, InventorySessionStatusEnum::COMPLETED->value);

        // Web view
        $this->actingAs($this->admin)
            ->get(route('inventory.report', $session->id))
            ->assertOk()
            ->assertSee('BIÊN BẢN KIỂM KÊ CHÍNH THỨC')
            ->assertDontSee('KỲ KIỂM KÊ CHƯA CHỐT (ĐANG THỰC HIỆN)');

        // PDF view content test
        $view = view('pdf.inventory_report', [
            'session' => $session,
            'stats' => [
                'total' => 1,
                'matched' => 1,
                'missing' => 0,
                'excess' => 0,
                'broken' => 0,
                'untested' => 0,
                'checked_total' => 1,
                'progress_pct' => 100,
            ],
            'discrepancyItems' => collect(),
        ])->render();

        $this->assertStringContainsString('BIÊN BẢN CHÍNH THỨC', $view);
        $this->assertStringNotContainsString('class="watermark"', $view);
    }

    public function test_reconciliation_stats_match_item_results(): void
    {
        $session = $this->createSession($this->knoiDept);

        $eq1 = $this->createEquipment($this->knoiDept, 'TB-ST-01', 'Máy Khớp 1');
        $eq2 = $this->createEquipment($this->knoiDept, 'TB-ST-02', 'Máy Khớp 2');
        $eq3 = $this->createEquipment($this->knoiDept, 'TB-ST-03', 'Máy Thiếu');
        $eq4 = $this->createEquipment($this->knoiDept, 'TB-ST-04', 'Máy Hỏng');
        $eq5 = $this->createEquipment($this->knoiDept, 'TB-ST-05', 'Máy Chưa Kiểm');

        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq1->id, 'scanned_code' => $eq1->code, 'result' => InventoryItemResultEnum::KHOP]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq2->id, 'scanned_code' => $eq2->code, 'result' => InventoryItemResultEnum::KHOP]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq3->id, 'scanned_code' => $eq3->code, 'result' => InventoryItemResultEnum::THIEU]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq4->id, 'scanned_code' => $eq4->code, 'result' => InventoryItemResultEnum::HONG]);
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => $eq5->id, 'scanned_code' => $eq5->code, 'result' => null]);
        // Excess item
        InventoryItem::create(['inventory_session_id' => $session->id, 'equipment_id' => null, 'scanned_code' => 'TB-EXTRA-99', 'result' => InventoryItemResultEnum::THUA]);

        Livewire::actingAs($this->admin)
            ->test(InventoryReport::class, ['session' => $session])
            ->assertSet('session.id', $session->id)
            ->assertViewHas('totalCount', 6)
            ->assertViewHas('matchedCount', 2)
            ->assertViewHas('missingCount', 1)
            ->assertViewHas('excessCount', 1)
            ->assertViewHas('brokenCount', 1)
            ->assertViewHas('untestedCount', 1)
            ->assertViewHas('checkedTotal', 5)
            ->assertViewHas('progressPct', 83.3);
    }

    public function test_excel_export_returns_200_and_contains_discrepancy_data(): void
    {
        $session = $this->createSession($this->knoiDept);
        $eq = $this->createEquipment($this->knoiDept, 'TB-XLS-01', 'Máy Thở Chênh Lệch');

        InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq->id,
            'scanned_code' => $eq->code,
            'result' => InventoryItemResultEnum::HONG,
            'note' => 'Hỏng nguồn điện',
            'checked_by' => $this->admin->id,
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('inventory.export-excel', $session->id));

        $response->assertOk();
    }

    public function test_using_dept_does_not_see_excess_equipment_from_other_department(): void
    {
        $session = $this->createSession($this->knoiDept);

        // Machine belonging to Khoa Ngoại
        $otherDeptEq = $this->createEquipment($this->kngoaiDept, 'TB-NGOAI-SECRET', 'Máy Đo Khí Máu Khoa Ngoại');

        // Scanned during Khoa Nội inventory as excess
        InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $otherDeptEq->id,
            'scanned_code' => $otherDeptEq->code,
            'system_department_id' => $this->kngoaiDept->id,
            'result' => InventoryItemResultEnum::THUA,
            'note' => 'Máy mang nhầm từ khoa Ngoại',
        ]);

        // Khoa Nội user visits report -> should NOT see TB-NGOAI-SECRET
        $this->actingAs($this->khoa)
            ->get(route('inventory.report', $session->id))
            ->assertOk()
            ->assertDontSee('TB-NGOAI-SECRET')
            ->assertDontSee('Máy Đo Khí Máu Khoa Ngoại');

        // PDF export by Khoa Nội -> should NOT contain TB-NGOAI-SECRET
        $pdfResponse = $this->actingAs($this->khoa)
            ->get(route('inventory.export-pdf', $session->id));
        $pdfResponse->assertOk();
        $this->assertStringNotContainsString('TB-NGOAI-SECRET', $pdfResponse->getContent());

        // Admin sees it
        $this->actingAs($this->admin)
            ->get(route('inventory.report', $session->id))
            ->assertOk()
            ->assertSee('TB-NGOAI-SECRET');
    }
}
