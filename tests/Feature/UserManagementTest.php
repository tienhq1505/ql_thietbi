<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Profile\ChangePassword;
use App\Livewire\Users\UserIndex;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $vattu;

    protected User $kythuat;

    protected User $khoa;

    protected User $giamdoc;

    protected Department $knoiDept;

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
    }

    public function test_guest_redirected_to_login_when_accessing_users(): void
    {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_users_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Quản Lý Người Dùng');
    }

    public function test_other_roles_cannot_access_users_index_and_receive_403(): void
    {
        foreach ([$this->vattu, $this->kythuat, $this->khoa, $this->giamdoc] as $user) {
            $this->actingAs($user)
                ->get(route('users.index'))
                ->assertForbidden();
        }
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'Kỹ thuật viên Mới')
            ->set('email', 'ktv_moi@mems.local')
            ->set('password', 'password123')
            ->set('role', 'technician')
            ->set('department_id', null)
            ->call('createUser')
            ->assertHasNoErrors();

        $newUser = User::where('email', 'ktv_moi@mems.local')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Kỹ thuật viên Mới', $newUser->name);
        $this->assertTrue($newUser->hasRole('technician'));
        $this->assertEquals('active', $newUser->status);
    }

    public function test_create_user_with_using_dept_requires_department(): void
    {
        // Thiếu khoa -> lỗi
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'Bác sĩ Khoa Mới')
            ->set('email', 'bs_khoa@mems.local')
            ->set('password', 'password123')
            ->set('role', 'using_dept')
            ->set('department_id', null)
            ->call('createUser')
            ->assertHasErrors(['department_id']);

        $this->assertDatabaseMissing('users', ['email' => 'bs_khoa@mems.local']);

        // Có khoa -> thành công
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'Bác sĩ Khoa Mới')
            ->set('email', 'bs_khoa@mems.local')
            ->set('password', 'password123')
            ->set('role', 'using_dept')
            ->set('department_id', $this->knoiDept->id)
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'bs_khoa@mems.local')->first();
        $this->assertNotNull($user);
        $this->assertEquals($this->knoiDept->id, $user->department_id);
        $this->assertTrue($user->hasRole('using_dept'));
    }

    public function test_create_user_email_must_be_unique(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'Trùng Email')
            ->set('email', 'admin@mems.local') // Đã tồn tại
            ->set('password', 'password123')
            ->set('role', 'technician')
            ->call('createUser')
            ->assertHasErrors(['email']);
    }

    public function test_admin_can_edit_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $this->vattu->id)
            ->set('edit_name', 'Cán bộ Vật tư Cập nhật')
            ->set('edit_email', 'vattu_updated@mems.local')
            ->set('edit_role', 'equipment_dept')
            ->set('edit_department_id', null)
            ->call('updateUser')
            ->assertHasNoErrors();

        $this->vattu->refresh();
        $this->assertEquals('Cán bộ Vật tư Cập nhật', $this->vattu->name);
        $this->assertEquals('vattu_updated@mems.local', $this->vattu->email);
    }

    public function test_edit_user_to_using_dept_requires_department(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $this->kythuat->id)
            ->set('edit_role', 'using_dept')
            ->set('edit_department_id', null)
            ->call('updateUser')
            ->assertHasErrors(['edit_department_id']);
    }

    public function test_admin_can_lock_and_unlock_user(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(UserIndex::class);

        // Khóa tài khoản
        $component->call('toggleStatus', $this->kythuat->id);
        $this->kythuat->refresh();
        $this->assertEquals('locked', $this->kythuat->status);

        // Mở khóa tài khoản
        $component->call('toggleStatus', $this->kythuat->id);
        $this->kythuat->refresh();
        $this->assertEquals('active', $this->kythuat->status);
    }

    public function test_locked_user_cannot_login(): void
    {
        $this->kythuat->update(['status' => 'locked']);

        Livewire::test(Login::class)
            ->set('email', 'kythuat@mems.local')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertFalse(Auth::check());
    }

    public function test_locked_user_active_session_is_terminated_by_middleware(): void
    {
        $this->kythuat->update(['status' => 'locked']);

        // Khi truy cập qua middleware với user đã bị khóa -> bị logout và chuyển hướng về /login
        $this->actingAs($this->kythuat)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertFalse(Auth::check());
    }

    public function test_admin_cannot_self_lock(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('toggleStatus', $this->admin->id);

        $this->admin->refresh();
        $this->assertEquals('active', $this->admin->status);
    }

    public function test_admin_cannot_self_delete(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('deleteUser', $this->admin->id);

        $this->admin->refresh();
        $this->assertNull($this->admin->deleted_at);
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $this->admin->id)
            ->set('edit_role', 'technician')
            ->call('updateUser')
            ->assertHasErrors(['edit_role']);

        $this->admin->refresh();
        $this->assertTrue($this->admin->hasRole('admin'));
    }

    public function test_last_admin_cannot_be_locked_deleted_or_demoted(): void
    {
        // Hiện tại chỉ có 1 admin duy nhất trong hệ thống
        $secondAdmin = User::factory()->create([
            'email' => 'admin2@mems.local',
            'status' => 'active',
        ]);
        $secondAdmin->assignRole('admin');

        // Thao tác với admin thứ hai bởi admin 1
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);

        // Xóa admin thứ hai -> thành công vì còn admin 1
        $component->call('deleteUser', $secondAdmin->id);
        $this->assertNotNull($secondAdmin->fresh()->deleted_at);

        // Bây giờ chỉ còn lại duy nhất admin 1, thử thao tác khóa admin 1 -> bị chặn
        $component->call('toggleStatus', $this->admin->id);
        $this->assertEquals('active', $this->admin->fresh()->status);
    }

    public function test_admin_can_reset_user_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openResetModal', $this->kythuat->id)
            ->set('new_password', 'newsecretpass123')
            ->call('resetPassword')
            ->assertHasNoErrors();

        $this->kythuat->refresh();
        $this->assertTrue(Hash::check('newsecretpass123', $this->kythuat->password));

        // Kiểm tra đăng nhập với mật khẩu mới
        Livewire::test(Login::class)
            ->set('email', 'kythuat@mems.local')
            ->set('password', 'newsecretpass123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/dashboard');
    }

    public function test_soft_delete_preserves_audit_logs_without_errors(): void
    {
        // Tạo audit log gắn với user kythuat
        AuditLog::create([
            'user_id' => $this->kythuat->id,
            'action' => 'updated',
            'auditable_type' => Department::class,
            'auditable_id' => $this->knoiDept->id,
            'old_values' => ['name' => 'Cũ'],
            'new_values' => ['name' => 'Mới'],
        ]);

        // Admin xóa mềm kythuat
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('deleteUser', $this->kythuat->id);

        $this->kythuat->refresh();
        $this->assertNotNull($this->kythuat->deleted_at);

        // Bảng audit_log vẫn giữ nguyên record và không bị lỗi khóa ngoại
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->kythuat->id,
            'action' => 'updated',
        ]);
    }

    public function test_authenticated_user_can_change_own_password(): void
    {
        $this->actingAs($this->khoa)
            ->get(route('profile.password'))
            ->assertOk()
            ->assertSee('Đổi Mật Khẩu Cá Nhân');

        Livewire::actingAs($this->khoa)
            ->test(ChangePassword::class)
            ->set('current_password', 'password')
            ->set('password', 'khoaNewPass888')
            ->set('password_confirmation', 'khoaNewPass888')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->khoa->refresh();
        $this->assertTrue(Hash::check('khoaNewPass888', $this->khoa->password));

        // Đăng nhập lại với mật khẩu mới
        Livewire::test(Login::class)
            ->set('email', 'khoa@mems.local')
            ->set('password', 'khoaNewPass888')
            ->call('login')
            ->assertHasNoErrors();
    }

    public function test_change_password_requires_correct_current_password_and_valid_new_password(): void
    {
        // Sai mật khẩu hiện tại
        Livewire::actingAs($this->khoa)
            ->test(ChangePassword::class)
            ->set('current_password', 'wrongpassword')
            ->set('password', 'validnewpass123')
            ->set('password_confirmation', 'validnewpass123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        // Mật khẩu mới < 8 ký tự
        Livewire::actingAs($this->khoa)
            ->test(ChangePassword::class)
            ->set('current_password', 'password')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        // Mật khẩu mới không khớp xác nhận
        Livewire::actingAs($this->khoa)
            ->test(ChangePassword::class)
            ->set('current_password', 'password')
            ->set('password', 'validnewpass123')
            ->set('password_confirmation', 'mismatch123')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        // Mật khẩu mới trùng mật khẩu cũ
        Livewire::actingAs($this->khoa)
            ->test(ChangePassword::class)
            ->set('current_password', 'password')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    }

    // ===================================================================
    // Requirement 1: Kiểm tra quyền ở đầu từng hành động
    // ===================================================================

    public function test_revoked_admin_cannot_create_user(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('createUser')->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'newuser@mems.local']);
    }

    public function test_revoked_admin_cannot_open_create_modal(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('openCreateModal')->assertForbidden();
    }

    public function test_revoked_admin_cannot_open_edit_modal(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('openEditModal', $targetUser->id)->assertForbidden();
    }

    public function test_revoked_admin_cannot_update_user(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('updateUser')->assertForbidden();

        $this->assertNotEquals('Hacked Name', $targetUser->fresh()->name);
    }

    public function test_revoked_admin_cannot_open_reset_modal(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('openResetModal', $targetUser->id)->assertForbidden();
    }

    public function test_revoked_admin_cannot_reset_password(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('resetPassword')->assertForbidden();

        $this->assertFalse(Hash::check('hackedpass123', $targetUser->fresh()->password));
    }

    public function test_revoked_admin_cannot_toggle_status(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('toggleStatus', $targetUser->id)->assertForbidden();
        $this->assertEquals('active', $targetUser->fresh()->status);
    }

    public function test_revoked_admin_cannot_delete_user(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('technician');

        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->call('deleteUser', $targetUser->id)->assertForbidden();
        $this->assertNull($targetUser->fresh()->deleted_at);
    }

    public function test_revoked_admin_cannot_search_and_receives_403(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->set('search', 'something')->assertForbidden();
    }

    public function test_revoked_admin_cannot_change_filters_and_receives_403(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->set('roleFilter', 'admin')->assertForbidden();
    }

    public function test_revoked_admin_cannot_change_department_filter_and_receives_403(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->set('departmentFilter', (string) $this->knoiDept->id)->assertForbidden();
    }

    public function test_revoked_admin_cannot_change_status_filter_and_receives_403(): void
    {
        $component = Livewire::actingAs($this->admin)->test(UserIndex::class);
        $this->admin->syncRoles(['technician']);

        $component->set('statusFilter', 'locked')->assertForbidden();
    }

    // ===================================================================
    // Requirement 2: Audit Log tường minh
    // ===================================================================

    public function test_create_user_audit_log_contains_role_and_department(): void
    {
        $auditCountBefore = AuditLog::where('auditable_type', User::class)
            ->where('action', 'created')
            ->count();

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'User Audit Test')
            ->set('email', 'audit_test@mems.local')
            ->set('password', 'password123')
            ->set('role', 'using_dept')
            ->set('department_id', $this->knoiDept->id)
            ->call('createUser')
            ->assertHasNoErrors();

        $newUser = User::where('email', 'audit_test@mems.local')->first();
        $this->assertNotNull($newUser);

        // Kiểm tra audit log tạo mới
        $auditLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $newUser->id)
            ->where('action', 'created')
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->user_id);

        // Phải chứa role và department
        $this->assertEquals('using_dept', $auditLog->new_values['role']);
        $this->assertEquals($this->knoiDept->id, $auditLog->new_values['department_id']);
        $this->assertEquals($this->knoiDept->name, $auditLog->new_values['department']);

        // KHÔNG được chứa password
        $this->assertArrayNotHasKey('password', $auditLog->new_values);

        // Chỉ có đúng 1 bản ghi audit "created" mới
        $auditCountAfter = AuditLog::where('auditable_type', User::class)
            ->where('action', 'created')
            ->count();
        $this->assertEquals($auditCountBefore + 1, $auditCountAfter);
    }

    public function test_update_role_only_creates_exactly_one_audit_log_with_old_and_new_role(): void
    {
        // Chuẩn bị user mục tiêu
        $target = User::factory()->create(['status' => 'active']);
        $target->assignRole('technician');

        // Đếm audit logs trước khi đổi
        $auditCountBefore = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->count();

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $target->id)
            ->set('edit_role', 'equipment_dept')
            // Giữ nguyên name, email, department_id
            ->call('updateUser')
            ->assertHasNoErrors();

        // Chỉ có đúng 1 audit log mới
        $newAudits = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('id', '>', $auditCountBefore > 0
                ? AuditLog::where('auditable_type', User::class)
                    ->where('auditable_id', $target->id)
                    ->orderBy('id')
                    ->skip($auditCountBefore - 1)
                    ->first()->id
                : 0)
            ->get();

        // Phải có audit log chứa role
        $roleAudit = $newAudits->first(function ($log) {
            return isset($log->old_values['role']) && isset($log->new_values['role']);
        });

        $this->assertNotNull($roleAudit, 'Phải có audit log ghi nhận thay đổi vai trò.');
        $this->assertEquals('technician', $roleAudit->old_values['role']);
        $this->assertEquals('equipment_dept', $roleAudit->new_values['role']);

        // User thực sự đã đổi role
        $target->refresh();
        $this->assertTrue($target->hasRole('equipment_dept'));
    }

    public function test_update_department_creates_exactly_one_audit_log_with_department_names(): void
    {
        $otherDept = Department::where('code', '!=', 'KNOI')->first();
        if (! $otherDept) {
            $this->markTestSkipped('Cần ít nhất 2 khoa để test đổi khoa.');
        }

        $target = User::factory()->create([
            'status' => 'active',
            'department_id' => $this->knoiDept->id,
        ]);
        $target->assignRole('using_dept');

        $auditCountBefore = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->count();

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $target->id)
            ->set('edit_department_id', $otherDept->id)
            ->call('updateUser')
            ->assertHasNoErrors();

        // Đổi khoa chỉ được ghi ĐÚNG 1 dòng audit mới (có tên khoa cũ -> mới)
        $newAudits = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->where('action', 'updated')
            ->get();

        $this->assertCount(1, $newAudits, 'Đổi khoa chỉ được ghi đúng 1 dòng audit log mới.');

        $deptAudit = $newAudits->first();
        $this->assertNotNull($deptAudit, 'Phải có audit log ghi nhận thay đổi khoa.');
        $this->assertEquals($this->knoiDept->id, $deptAudit->old_values['department_id']);
        $this->assertEquals($otherDept->id, $deptAudit->new_values['department_id']);
        $this->assertEquals($this->knoiDept->name, $deptAudit->old_values['department']);
        $this->assertEquals($otherDept->name, $deptAudit->new_values['department']);
    }

    // ===================================================================
    // Requirement 3: isLastAdmin - admin đã khóa xóa được
    // ===================================================================

    public function test_locked_admin_can_be_deleted_when_active_admin_exists(): void
    {
        // Tạo admin thứ 2 rồi khóa
        $lockedAdmin = User::factory()->create([
            'email' => 'admin_locked@mems.local',
            'status' => 'locked',
        ]);
        $lockedAdmin->assignRole('admin');

        // Admin chính (active) xóa admin đã khóa -> thành công
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('deleteUser', $lockedAdmin->id);

        $this->assertNotNull($lockedAdmin->fresh()->deleted_at);
    }

    public function test_cannot_create_or_update_user_with_email_of_soft_deleted_user(): void
    {
        $delUser = User::factory()->create(['email' => 'deleted_member@mems.local']);
        $delUser->delete();
        $this->assertSoftDeleted('users', ['id' => $delUser->id]);

        // Tạo mới với email của user đã xóa mềm -> bị chặn
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->set('name', 'Thành viên mới')
            ->set('email', 'deleted_member@mems.local')
            ->set('password', 'password123')
            ->set('role', 'equipment_dept')
            ->call('createUser')
            ->assertHasErrors(['email']);

        // Sửa email thành viên khác thành email của user đã xóa mềm -> bị chặn
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $this->kythuat->id)
            ->set('edit_email', 'deleted_member@mems.local')
            ->call('updateUser')
            ->assertHasErrors(['edit_email']);
    }

    public function test_admin_reset_password_records_audit_log_without_revealing_password(): void
    {
        $target = $this->kythuat;
        $auditCountBefore = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->count();

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('openResetModal', $target->id)
            ->set('new_password', 'NewSuperSecret123')
            ->call('resetPassword')
            ->assertHasNoErrors();

        $audit = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $target->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($this->admin->id, $audit->user_id);
        $this->assertEquals('updated', $audit->action);

        // Đảm bảo không chứa mật khẩu thô hay chuỗi nhạy cảm
        $json = json_encode($audit->toArray());
        $this->assertStringNotContainsString('NewSuperSecret123', $json);
    }
}
