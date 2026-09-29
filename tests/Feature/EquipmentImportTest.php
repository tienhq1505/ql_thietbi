<?php

namespace Tests\Feature;

use App\Livewire\Equipment\EquipmentImport;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\ImportBatch;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $vattuUser;

    protected User $techUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $department = Department::create([
            'code' => 'KCC',
            'name' => 'Khoa Cấp Cứu',
        ]);

        Location::create([
            'code' => 'LOC-KCC-01',
            'name' => 'Phòng Cấp Cứu 1',
            'department_id' => $department->id,
        ]);

        $category = EquipmentCategory::create(['code' => 'CAT-01', 'name' => 'Chẩn đoán hình ảnh']);
        $manufacturer = Manufacturer::create(['code' => 'MFG-01', 'name' => 'Philips']);
        Supplier::create(['code' => 'NCC-VIETMED', 'name' => 'Công ty Việt Med']);

        EquipmentModel::create([
            'code' => 'PHIL-MX450',
            'name' => 'Monitor Philips MX450',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);

        $this->adminUser = User::factory()->create(['department_id' => $department->id]);
        $this->adminUser->assignRole('admin');

        $this->vattuUser = User::factory()->create(['department_id' => $department->id]);
        $this->vattuUser->assignRole('equipment_dept');

        $this->techUser = User::factory()->create(['department_id' => $department->id]);
        $this->techUser->assignRole('technician');
    }

    public function test_authorized_users_can_access_import_pages(): void
    {
        $this->actingAs($this->vattuUser)
            ->get(route('equipment.import'))
            ->assertStatus(200);

        $this->actingAs($this->vattuUser)
            ->get(route('import-batches.index'))
            ->assertStatus(200);
    }

    public function test_unauthorized_users_cannot_access_import_pages(): void
    {
        $this->actingAs($this->techUser)
            ->get(route('equipment.import'))
            ->assertStatus(403);

        $this->actingAs($this->techUser)
            ->get(route('import-batches.index'))
            ->assertStatus(403);
    }

    public function test_full_import_flow_via_livewire(): void
    {
        Artisan::call('equipment:generate-sample-import');

        $samplePath = storage_path('app/samples/sample_import.xlsx');
        $uploadedFile = UploadedFile::fake()->createWithContent('sample_import.xlsx', file_get_contents($samplePath));

        Livewire::actingAs($this->adminUser)
            ->test(EquipmentImport::class)
            ->set('file', $uploadedFile)
            ->call('uploadAndPreview')
            ->assertSet('step', 'preview')
            ->assertSet('summary.total', 10)
            ->assertSet('summary.valid', 6)
            ->assertSet('summary.error', 4)
            ->call('confirmImport')
            ->assertSet('step', 'done');

        $this->assertDatabaseCount('equipment', 6);
        $this->assertDatabaseCount('import_batches', 1);

        $batch = ImportBatch::first();
        $this->assertEquals(6, $batch->success_rows);
        $this->assertEquals(4, $batch->failed_rows);

        $importedEquipment = Equipment::where('code', 'EQ-SMP-001')->first();
        $this->assertNotNull($importedEquipment);
        $this->assertNotNull($importedEquipment->location_id);
    }
}
