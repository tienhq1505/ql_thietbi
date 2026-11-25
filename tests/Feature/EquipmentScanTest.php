<?php

namespace Tests\Feature;

use App\Enums\EquipmentConditionEnum;
use App\Enums\InventoryItemResultEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Livewire\Auth\Login;
use App\Livewire\Equipment\EquipmentScan;
use App\Livewire\Inventory\InventoryShow;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentScanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $vattu;

    protected User $kythuat;

    protected User $khoa;

    protected User $giamdoc;

    protected Department $knoiDept;

    protected Department $kngoaiDept;

    protected Equipment $knoiEquipment;

    protected Equipment $kngoaiEquipment;

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
        $this->kngoaiDept = Department::where('code', '!=', 'KNOI')->firstOrFail();

        // Tạo máy thuộc Khoa Nội
        $this->knoiEquipment = Equipment::factory()->create([
            'code' => 'TB-KNOI-001',
            'name' => 'Máy Monitor Theo Dõi Khoa Nội',
            'department_id' => $this->knoiDept->id,
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
            'purchase_price' => 150000000,
            'funding_source' => 'NganSachBiMat12345',
        ]);

        // Tạo máy thuộc Khoa Ngoại
        $this->kngoaiEquipment = Equipment::factory()->create([
            'code' => 'TB-KNGOAI-002',
            'name' => 'Máy Phẫu Thuật Khoa Ngoại',
            'department_id' => $this->kngoaiDept->id,
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
        ]);
    }

    public function test_guest_redirected_to_login_and_equipment_info_is_not_leaked(): void
    {
        $response = $this->get('/q/'.$this->knoiEquipment->code);

        $response->assertRedirect(route('login'));

        $loginResponse = $this->followRedirects($response);
        $loginResponse->assertOk();

        // Đảm bảo thông tin máy không rò rỉ trên trang đăng nhập
        $loginResponse->assertDontSee($this->knoiEquipment->name);
        $loginResponse->assertDontSee($this->knoiEquipment->code);
        $loginResponse->assertDontSee('NganSachBiMat12345');
    }

    public function test_guest_redirected_back_to_intended_scan_url_after_login(): void
    {
        $scanUrl = url('/q/'.$this->knoiEquipment->code);

        // Khách truy cập đường dẫn /q/{code}
        $this->get($scanUrl)->assertRedirect(route('login'));

        // Đăng nhập qua Livewire
        Livewire::test(Login::class)
            ->set('email', 'admin@mems.local')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect($scanUrl);
    }

    public function test_non_existent_code_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get('/q/NON-EXISTENT-CODE-9999')
            ->assertNotFound();
    }

    public function test_using_dept_can_view_own_department_equipment(): void
    {
        $this->actingAs($this->khoa)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertSee($this->knoiEquipment->name)
            ->assertSee($this->knoiEquipment->code);
    }

    public function test_using_dept_cannot_view_other_department_equipment_and_receives_403(): void
    {
        $response = $this->actingAs($this->khoa)
            ->get('/q/'.$this->kngoaiEquipment->code);

        $response->assertForbidden();

        // Không lộ tên hay thông tin máy khoa khác
        $response->assertDontSee($this->kngoaiEquipment->name);
    }

    public function test_directorate_can_view_equipment(): void
    {
        $this->actingAs($this->giamdoc)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertSee($this->knoiEquipment->name);
    }

    public function test_code_with_special_characters_or_spaces(): void
    {
        $specialEquipment = Equipment::factory()->create([
            'code' => 'TB-DAC BIET/01',
            'name' => 'Máy Đặc Biệt Có Dấu Cách Và Gạch Chéo',
            'department_id' => $this->knoiDept->id,
        ]);

        $this->actingAs($this->admin)
            ->get('/q/'.rawurlencode('TB-DAC BIET/01'))
            ->assertOk()
            ->assertSee('Máy Đặc Biệt Có Dấu Cách Và Gạch Chéo');
    }

    public function test_equipment_card_does_not_contain_purchase_price_or_funding_source(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/q/'.$this->knoiEquipment->code);

        $response->assertOk();
        $response->assertDontSee('150000000');
        $response->assertDontSee('150,000,000');
        $response->assertDontSee('NganSachBiMat12345');
    }

    public function test_technician_and_admin_see_change_condition_button(): void
    {
        $this->actingAs($this->kythuat)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertSee('Đổi Tình Trạng Thiết Bị');

        $this->actingAs($this->admin)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertSee('Đổi Tình Trạng Thiết Bị');
    }

    public function test_using_dept_and_directorate_do_not_see_change_condition_button(): void
    {
        $this->actingAs($this->khoa)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertDontSee('Đổi Tình Trạng Thiết Bị');

        $this->actingAs($this->giamdoc)
            ->get('/q/'.$this->knoiEquipment->code)
            ->assertOk()
            ->assertDontSee('Đổi Tình Trạng Thiết Bị');
    }

    public function test_authorized_user_can_update_condition_via_quick_view(): void
    {
        Livewire::actingAs($this->kythuat)
            ->test(EquipmentScan::class, ['code' => $this->knoiEquipment->code])
            ->call('openConditionModal')
            ->assertSet('showConditionModal', true)
            ->set('newCondition', EquipmentConditionEnum::HONG->value)
            ->set('conditionReason', 'Hỏng bo mạch nguồn')
            ->set('conditionNote', 'Cần liên hệ nhà cung cấp thay thế linh kiện')
            ->call('saveCondition')
            ->assertHasNoErrors()
            ->assertSet('showConditionModal', false);

        $this->knoiEquipment->refresh();
        $this->assertEquals(EquipmentConditionEnum::HONG, $this->knoiEquipment->condition);

        $this->assertDatabaseHas('equipment_condition_history', [
            'equipment_id' => $this->knoiEquipment->id,
            'new_condition' => EquipmentConditionEnum::HONG->value,
            'reason' => 'Hỏng bo mạch nguồn',
            'checked_by' => $this->kythuat->id,
        ]);
    }

    public function test_unauthorized_user_cannot_save_condition(): void
    {
        Livewire::actingAs($this->khoa)
            ->test(EquipmentScan::class, ['code' => $this->knoiEquipment->code])
            ->set('newCondition', EquipmentConditionEnum::HONG->value)
            ->set('conditionReason', 'Khoa tự đổi')
            ->call('saveCondition')
            ->assertForbidden();
    }

    public function test_new_label_contains_full_url(): void
    {
        $expectedUrl = rtrim(config('app.url'), '/').'/q/'.rawurlencode($this->knoiEquipment->code);

        $response = $this->actingAs($this->admin)
            ->get(route('equipment.label', $this->knoiEquipment->id));

        $response->assertOk();
        // SVG mã QR mới được tạo từ đường dẫn $expectedUrl
        $this->assertTrue(str_contains($expectedUrl, '/q/'.$this->knoiEquipment->code));
    }

    public function test_inventory_scan_screen_accepts_both_url_and_raw_code(): void
    {
        $session = InventorySession::create([
            'name' => 'Kỳ Kiểm Kê Test QR',
            'department_id' => $this->knoiDept->id,
            'start_date' => now()->startOfDay(),
            'end_date' => now()->addDays(7)->endOfDay(),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $item1 = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $this->knoiEquipment->id,
            'system_department_id' => $this->knoiDept->id,
        ]);

        $eq2 = Equipment::factory()->create([
            'code' => 'TB-KNOI-999',
            'department_id' => $this->knoiDept->id,
            'condition' => EquipmentConditionEnum::DANG_HOAT_DONG,
        ]);

        $item2 = InventoryItem::create([
            'inventory_session_id' => $session->id,
            'equipment_id' => $eq2->id,
            'system_department_id' => $this->knoiDept->id,
        ]);

        // 1. Quét bằng đường dẫn URL dạng .../q/{code}
        $fullUrl = 'http://localhost:8000/q/'.rawurlencode($this->knoiEquipment->code);
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->call('handleScannedCode', $fullUrl)
            ->assertHasNoErrors();

        $this->assertEquals(InventoryItemResultEnum::KHOP, $item1->fresh()->result);

        // 2. Quét bằng mã thô truyền thống
        Livewire::actingAs($this->admin)
            ->test(InventoryShow::class, ['session' => $session])
            ->call('handleScannedCode', $eq2->code)
            ->assertHasNoErrors();

        $this->assertEquals(InventoryItemResultEnum::KHOP, $item2->fresh()->result);
    }

    public function test_remember_me_is_checked_by_default_on_login(): void
    {
        Livewire::test(Login::class)
            ->assertSet('remember', true);
    }

    public function test_equipment_scan_uses_mobile_layout_without_aside(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/q/'.$this->knoiEquipment->code);

        $response->assertOk();
        // Không có thẻ <aside> trên giao diện di động
        $response->assertDontSee('<aside', false);
        // Có thanh trên cùng gọn với tên hệ thống, link dashboard và form đăng xuất
        $response->assertSee('MEMS Hospital');
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('logout'));
    }

    public function test_route_equipment_scan_is_declared_only_once(): void
    {
        $content = file_get_contents(base_path('routes/web.php'));
        $this->assertEquals(1, substr_count($content, "Route::get('/q/{code}'"));
    }

    public function test_label_contains_short_url_below_code(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('equipment.label', $this->knoiEquipment->id));

        $response->assertOk();
        $response->assertSee('<div class="label-code">'.$this->knoiEquipment->code.'</div>', false);
        $response->assertSee('<div class="label-url">/q/'.$this->knoiEquipment->code.'</div>', false);
    }
}
