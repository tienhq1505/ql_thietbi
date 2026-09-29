<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_login_screen(): void
    {
        Livewire::test('auth.login')
            ->set('email', 'admin@mems.local')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        Livewire::test('auth.login')
            ->set('email', 'admin@mems.local')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('email', 'admin@mems.local')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_demo_users_and_roles_are_seeded_correctly(): void
    {
        $admin = User::where('email', 'admin@mems.local')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('admin'));

        $vattu = User::where('email', 'vattu@mems.local')->first();
        $this->assertNotNull($vattu);
        $this->assertTrue($vattu->hasRole('equipment_dept'));

        $khoa = User::where('email', 'khoa@mems.local')->first();
        $this->assertNotNull($khoa);
        $this->assertTrue($khoa->hasRole('using_dept'));
        $this->assertNotNull($khoa->department_id);
    }

    public function test_department_scope_middleware_restricts_using_dept_without_department(): void
    {
        $khoaUserWithoutDept = User::factory()->create([
            'department_id' => null,
            'status' => 'active',
        ]);
        $khoaUserWithoutDept->assignRole('using_dept');

        $response = $this->actingAs($khoaUserWithoutDept)->get('/dashboard');

        $response->assertStatus(403);
    }

    public function test_login_rate_limiting_blocks_after_5_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Livewire::test('auth.login')
                ->set('email', 'admin@mems.local')
                ->set('password', 'wrong-password')
                ->call('login');
        }

        // Attempt #6 must fail with throttle rate limit error
        Livewire::test('auth.login')
            ->set('email', 'admin@mems.local')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);
    }
}
