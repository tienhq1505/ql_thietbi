<?php

namespace Tests\Feature;

use App\Enums\InventorySessionStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\InventorySession;
use App\Models\User;
use Database\Seeders\DemoEquipmentSeeder;
use Database\Seeders\DemoMasterDataSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(DemoMasterDataSeeder::class);
        $this->seed(DemoEquipmentSeeder::class);
    }

    public function test_all_routes_return_non_500_for_all_roles(): void
    {
        $equipment = Equipment::firstOrFail();
        $department = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->firstOrFail();

        $session = InventorySession::create([
            'name' => 'Kỳ kiểm kê smoke test',
            'department_id' => $department->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => InventorySessionStatusEnum::IN_PROGRESS,
            'created_by' => User::where('email', 'admin@mems.local')->first()->id,
        ]);

        $routesToTest = [
            '/dashboard',
            '/equipment',
            '/equipment/create',
            '/equipment/import',
            '/equipment/unassigned',
            '/equipment/'.$equipment->id,
            '/equipment/'.$equipment->id.'/label',
            '/equipment/'.$equipment->id.'/edit',
            '/q/'.$equipment->code,
            '/import-batches',
            '/categories',
            '/reports/documents',
            '/audit-logs',
            '/inventory',
            '/inventory/'.$session->id,
            '/inventory/'.$session->id.'/report',
            '/inventory/'.$session->id.'/export-pdf',
            '/inventory/'.$session->id.'/export-excel',
            '/inventory/'.$session->id.'/labels',
            '/departments/'.$department->id.'/equipment-labels',
            '/users',
            '/profile/password',
        ];

        $roles = [
            'admin' => 'admin@mems.local',
            'equipment_dept' => 'vattu@mems.local',
            'technician' => 'kythuat@mems.local',
            'using_dept' => 'khoa@mems.local',
            'directorate' => 'giamdoc@mems.local',
        ];

        foreach ($roles as $roleName => $email) {
            $user = User::where('email', $email)->firstOrFail();

            foreach ($routesToTest as $url) {
                $response = $this->actingAs($user)->get($url);
                $status = $response->getStatusCode();

                $this->assertNotEquals(
                    500,
                    $status,
                    "Route [{$url}] trả về 500 Server Error cho vai trò [{$roleName}]!"
                );

                $this->assertContains(
                    $status,
                    [200, 302, 403],
                    "Route [{$url}] trả về mã trạng thái bất thường [{$status}] cho vai trò [{$roleName}]!"
                );
            }
        }
    }
}
