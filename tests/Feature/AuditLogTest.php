<?php

namespace Tests\Feature;

use App\Livewire\AuditLogs\AuditLogIndex;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_audit_logs(): void
    {
        $admin = User::where('email', 'admin@mems.local')->firstOrFail();

        $response = $this->actingAs($admin)->get('/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Nhật Ký Hệ Thống (Audit Log)');
    }

    public function test_equipment_dept_can_access_audit_logs(): void
    {
        $vattu = User::where('email', 'vattu@mems.local')->firstOrFail();

        $response = $this->actingAs($vattu)->get('/audit-logs');
        $response->assertStatus(200);
    }

    public function test_directorate_can_access_audit_logs(): void
    {
        $giamdoc = User::where('email', 'giamdoc@mems.local')->firstOrFail();

        $response = $this->actingAs($giamdoc)->get('/audit-logs');
        $response->assertStatus(200);
    }

    public function test_technician_cannot_access_audit_logs(): void
    {
        $kythuat = User::where('email', 'kythuat@mems.local')->firstOrFail();

        $response = $this->actingAs($kythuat)->get('/audit-logs');
        $response->assertStatus(403);
    }

    public function test_using_dept_cannot_access_audit_logs(): void
    {
        $khoa = User::where('email', 'khoa@mems.local')->firstOrFail();

        $response = $this->actingAs($khoa)->get('/audit-logs');
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/audit-logs');
        $response->assertRedirect('/login');
    }

    public function test_audit_log_model_throws_exception_on_update(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'new_values' => ['name' => 'Test'],
            'created_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Audit logs are append-only and cannot be updated.');

        $log->update(['action' => 'updated']);
    }

    public function test_audit_log_model_throws_exception_on_delete(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'new_values' => ['name' => 'Test'],
            'created_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Audit logs are append-only and cannot be deleted.');

        $log->delete();
    }

    public function test_sqlite_trigger_prevents_raw_sql_update(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'new_values' => ['name' => 'Trigger Test'],
            'created_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::statement("UPDATE audit_logs SET action = 'tampered' WHERE id = {$log->id}");
    }

    public function test_sqlite_trigger_prevents_raw_sql_delete(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'new_values' => ['name' => 'Delete Test'],
            'created_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::statement("DELETE FROM audit_logs WHERE id = {$log->id}");
    }

    public function test_observer_records_creation_and_never_stores_passwords_or_tokens(): void
    {
        $user = User::create([
            'name' => 'Audit Test User',
            'email' => 'audit_test@example.com',
            'password' => bcrypt('SuperSecret123!'),
            'remember_token' => 'random_token_string',
        ]);

        $log = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($log, 'Audit log was not created for new user');
        $this->assertIsArray($log->new_values);
        $this->assertEquals('Audit Test User', $log->new_values['name']);
        $this->assertEquals('audit_test@example.com', $log->new_values['email']);

        // Check strict security: password & remember_token must never exist
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertArrayNotHasKey('remember_token', $log->new_values);
        $this->assertNull($log->old_values);
    }

    public function test_observer_records_equipment_update_and_delete(): void
    {
        $admin = User::where('email', 'admin@mems.local')->firstOrFail();
        $this->actingAs($admin);

        $department = Department::first();
        $category = EquipmentCategory::create(['name' => 'Máy theo dõi', 'code' => 'MTD-'.uniqid()]);
        $mfg = Manufacturer::create(['name' => 'Philips', 'code' => 'PHI-'.uniqid()]);
        $supplier = Supplier::create(['name' => 'Công ty Y tế ABC', 'code' => 'SUP-'.uniqid()]);
        $model = EquipmentModel::create([
            'name' => 'IntelliVue MX450',
            'code' => 'MOD-'.uniqid(),
            'category_id' => $category->id,
            'manufacturer_id' => $mfg->id,
        ]);
        $location = Location::create(['name' => 'Phòng Hồi sức', 'code' => 'LOC-'.uniqid(), 'department_id' => $department->id]);

        $equipment = Equipment::create([
            'name' => 'Máy theo dõi bệnh nhân MX450',
            'code' => 'TB-MTD-001',
            'serial_number' => 'SN-123456',
            'category_id' => $category->id,
            'manufacturer_id' => $mfg->id,
            'equipment_model_id' => $model->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'supplier_id' => $supplier->id,
            'status' => 'dang_su_dung',
            'condition' => 'dang_hoat_dong',
        ]);

        // Check created log
        $createLog = AuditLog::where('auditable_type', Equipment::class)
            ->where('auditable_id', $equipment->id)
            ->where('action', 'created')
            ->first();
        $this->assertNotNull($createLog);
        $this->assertEquals($admin->id, $createLog->user_id);

        // Update equipment
        $equipment->update([
            'serial_number' => 'SN-999999',
        ]);

        $updateLog = AuditLog::where('auditable_type', Equipment::class)
            ->where('auditable_id', $equipment->id)
            ->where('action', 'updated')
            ->first();
        $this->assertNotNull($updateLog);
        $this->assertEquals('SN-123456', $updateLog->old_values['serial_number']);
        $this->assertEquals('SN-999999', $updateLog->new_values['serial_number']);

        // Delete equipment
        $equipmentId = $equipment->id;
        $equipment->delete();

        $deleteLog = AuditLog::where('auditable_type', Equipment::class)
            ->where('auditable_id', $equipmentId)
            ->where('action', 'deleted')
            ->first();
        $this->assertNotNull($deleteLog);
        $this->assertEquals('TB-MTD-001', $deleteLog->old_values['code']);
    }

    public function test_livewire_audit_logs_component_renders_and_filters(): void
    {
        $admin = User::where('email', 'admin@mems.local')->firstOrFail();

        $log1 = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'new_values' => ['name' => 'Livewire User Test'],
            'created_at' => now(),
        ]);

        $log2 = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'deleted',
            'auditable_type' => Department::class,
            'auditable_id' => 99,
            'old_values' => ['name' => 'Khoa Test Delete'],
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogIndex::class)
            ->assertStatus(200)
            ->set('action', 'created')
            ->set('auditable_type', User::class)
            ->call('viewDetails', $log1->id)
            ->assertSee('Livewire User Test')
            ->call('closeModal')
            ->call('resetFilters')
            ->call('viewDetails', $log2->id)
            ->assertSee('Khoa Test Delete');
    }

    public function test_artisan_db_backup_command(): void
    {
        // Ensure database exists
        $dbPath = database_path('database.sqlite');
        if (! File::exists($dbPath)) {
            File::put($dbPath, 'test-sqlite-content');
        }

        $this->artisan('db:backup', ['--keep' => 3])
            ->assertExitCode(0)
            ->expectsOutputToContain('Đã tạo bản sao lưu thành công');

        $backupDir = storage_path('app/backups');
        $this->assertTrue(File::isDirectory($backupDir));

        $backupFiles = File::files($backupDir);
        $this->assertNotEmpty($backupFiles);
    }
}
