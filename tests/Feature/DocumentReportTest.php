<?php

namespace Tests\Feature;

use App\Livewire\Reports\DocumentReportIndex;
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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class DocumentReportTest extends TestCase
{
    use RefreshDatabase;

    private Department $deptNoi;

    private Department $deptSan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->deptNoi = Department::where('code', 'KNOI')->firstOrFail();
        $this->deptSan = Department::where('code', 'KSAN')->firstOrFail();

        $cat = EquipmentCategory::create(['code' => 'CAT-REP', 'name' => 'Loại report']);
        $mfg = Manufacturer::create(['code' => 'MFG-REP', 'name' => 'Hãng report']);
        $model = EquipmentModel::create([
            'code' => 'MOD-REP',
            'name' => 'Model report',
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
        ]);

        Equipment::create([
            'code' => 'TB-REP-NOI',
            'name' => 'Máy Khoa Nội',
            'equipment_model_id' => $model->id,
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
            'department_id' => $this->deptNoi->id,
        ]);

        Equipment::create([
            'code' => 'TB-REP-SAN',
            'name' => 'Máy Khoa Sản',
            'equipment_model_id' => $model->id,
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
            'department_id' => $this->deptSan->id,
        ]);
    }

    public function test_role_based_scoping_for_document_report(): void
    {
        // Khoa Nội user (using_dept role)
        $khoaNoiUser = User::factory()->create([
            'department_id' => $this->deptNoi->id,
            'status' => 'active',
        ]);
        $khoaNoiUser->assignRole('using_dept');

        Livewire::actingAs($khoaNoiUser)
            ->test(DocumentReportIndex::class)
            ->assertSee($this->deptNoi->name)
            ->assertDontSee($this->deptSan->name);

        // Admin user
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(DocumentReportIndex::class)
            ->assertSee($this->deptNoi->name)
            ->assertSee($this->deptSan->name);
    }

    public function test_excel_export_for_document_report(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $component = new DocumentReportIndex;
        $response = $component->exportExcel();

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }
}
