<?php

namespace App\Livewire\Users;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    // Filters
    public string $search = '';

    public string $roleFilter = '';

    public string $departmentFilter = '';

    public string $statusFilter = '';

    // Create Modal state
    public bool $showCreateModal = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'using_dept';

    public ?int $department_id = null;

    // Edit Modal state
    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $edit_name = '';

    public string $edit_email = '';

    public string $edit_role = '';

    public ?int $edit_department_id = null;

    // Reset Password Modal state
    public bool $showResetModal = false;

    public ?int $resetUserId = null;

    public string $resetUserName = '';

    public string $new_password = '';

    public function boot(): void
    {
        $this->authorizeManage();
    }

    public function hydrate(): void
    {
        $this->authorizeManage();
    }

    public function mount(): void
    {
        $this->authorizeManage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Kiểm tra quyền users.manage ở đầu từng hành động.
     */
    protected function authorizeManage(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can('users.manage')) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
    }

    protected function isLastAdmin(User $targetUser): bool
    {
        // Chỉ coi là "admin cuối cùng" khi đối tượng là admin đang hoạt động
        // và là admin hoạt động duy nhất
        if (! $targetUser->hasRole('admin') || $targetUser->status !== 'active') {
            return false;
        }

        $activeAdminCount = User::role('admin')
            ->where('status', 'active')
            ->count();

        return $activeAdminCount <= 1;
    }

    public function openCreateModal(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'using_dept';
        $this->department_id = null;
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $this->authorizeManage();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,equipment_dept,technician,using_dept,directorate',
            'department_id' => $this->role === 'using_dept'
                ? 'required|exists:departments,id'
                : 'nullable|exists:departments,id',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng (kể cả tài khoản đã xóa).',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò người dùng.',
            'department_id.required' => 'Vai trò Khoa sử dụng bắt buộc phải chọn khoa/phòng.',
        ]);

        $user = User::withoutEvents(function () {
            return User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'department_id' => $this->department_id ?: null,
                'status' => 'active',
            ]);
        });

        $user->assignRole($this->role);

        // Ghi audit log tường minh kèm role + department
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role' => $this->role,
                'department_id' => $user->department_id,
                'department' => $user->department?->name ?? 'Toàn viện',
            ],
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);

        $this->showCreateModal = false;
        $this->reset(['name', 'email', 'password', 'role', 'department_id']);
        session()->flash('message', "Đã tạo tài khoản thành công cho người dùng {$user->name}.");
    }

    public function openEditModal(int $userId): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_email = $user->email;
        $this->edit_role = $user->roles->first()?->name ?? 'using_dept';
        $this->edit_department_id = $user->department_id;
        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->authorizeManage();

        $targetUser = User::findOrFail($this->editingUserId);

        // Chặn tự hại: admin không được tự gỡ vai trò admin của mình
        if ($targetUser->id === auth()->id() && $this->edit_role !== 'admin') {
            $this->addError('edit_role', 'Bạn không thể tự gỡ vai trò Quản trị hệ thống của chính mình.');

            return;
        }

        // Chặn hạ quyền admin cuối cùng
        if ($this->isLastAdmin($targetUser) && $this->edit_role !== 'admin') {
            $this->addError('edit_role', 'Không thể gỡ vai trò Quản trị hệ thống của quản trị viên duy nhất còn lại.');

            return;
        }

        $this->validate([
            'edit_name' => 'required|string|max:255',
            'edit_email' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
            'edit_role' => 'required|in:admin,equipment_dept,technician,using_dept,directorate',
            'edit_department_id' => $this->edit_role === 'using_dept'
                ? 'required|exists:departments,id'
                : 'nullable|exists:departments,id',
        ], [
            'edit_name.required' => 'Vui lòng nhập họ và tên.',
            'edit_email.required' => 'Vui lòng nhập địa chỉ email.',
            'edit_email.email' => 'Địa chỉ email không hợp lệ.',
            'edit_email.unique' => 'Địa chỉ email này đã được sử dụng (kể cả tài khoản đã xóa).',
            'edit_role.required' => 'Vui lòng chọn vai trò người dùng.',
            'edit_department_id.required' => 'Vai trò Khoa sử dụng bắt buộc phải chọn khoa/phòng.',
        ]);

        $oldRole = $targetUser->roles->first()?->name;
        $newRole = $this->edit_role;
        $oldDepartmentId = $targetUser->department_id;
        $newDepartmentId = $this->edit_department_id ?: null;

        $targetUser->update([
            'name' => $this->edit_name,
            'email' => $this->edit_email,
            'department_id' => $newDepartmentId,
        ]);

        // Ghi audit log tường minh cho thay đổi vai trò
        if ($oldRole !== $newRole) {
            $targetUser->syncRoles([$newRole]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'auditable_type' => User::class,
                'auditable_id' => $targetUser->id,
                'old_values' => ['role' => $oldRole],
                'new_values' => ['role' => $newRole],
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        }

        // Ghi audit log tường minh cho thay đổi khoa
        if ($oldDepartmentId !== $newDepartmentId) {
            $oldDeptName = $oldDepartmentId ? Department::find($oldDepartmentId)?->name : 'Toàn viện';
            $newDeptName = $newDepartmentId ? Department::find($newDepartmentId)?->name : 'Toàn viện';

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'auditable_type' => User::class,
                'auditable_id' => $targetUser->id,
                'old_values' => ['department_id' => $oldDepartmentId, 'department' => $oldDeptName],
                'new_values' => ['department_id' => $newDepartmentId, 'department' => $newDeptName],
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        }

        $this->showEditModal = false;
        session()->flash('message', "Đã cập nhật thông tin người dùng {$targetUser->name} thành công.");
    }

    public function openResetModal(int $userId): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $user = User::findOrFail($userId);

        $this->resetUserId = $user->id;
        $this->resetUserName = $user->name;
        $this->new_password = '';
        $this->showResetModal = true;
    }

    public function resetPassword(): void
    {
        $this->authorizeManage();

        $this->validate([
            'new_password' => 'required|string|min:8',
        ], [
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
        ]);

        $targetUser = User::findOrFail($this->resetUserId);
        $targetUser->update([
            'password' => Hash::make($this->new_password),
        ]);

        $targetUser->setRememberToken(Str::random(60));
        $targetUser->save();

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $targetUser->id)->delete();
        }

        // Ghi Audit Log khi đặt lại mật khẩu (không lưu mật khẩu mới)
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $targetUser->id,
            'old_values' => ['reset_password' => 'Mật khẩu cũ'],
            'new_values' => ['reset_password' => 'Đã đặt lại mật khẩu mới'],
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);

        $this->new_password = '';
        $this->showResetModal = false;
        session()->flash('message', "Đã đặt lại mật khẩu mới cho người dùng {$targetUser->name}.");
    }

    public function toggleStatus(int $userId): void
    {
        $this->authorizeManage();

        $targetUser = User::findOrFail($userId);

        if ($targetUser->status === 'active') {
            // Chặn tự khóa
            if ($targetUser->id === auth()->id()) {
                session()->flash('error', 'Bạn không thể tự khóa tài khoản của chính mình.');

                return;
            }

            // Chặn khóa admin cuối cùng
            if ($this->isLastAdmin($targetUser)) {
                session()->flash('error', 'Không thể khóa tài khoản quản trị viên duy nhất còn lại của hệ thống.');

                return;
            }

            $targetUser->update(['status' => 'locked']);
            $targetUser->setRememberToken(Str::random(60));
            $targetUser->save();

            // Vô hiệu phiên làm việc hiện tại
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $targetUser->id)->delete();
            }

            session()->flash('message', "Đã khóa tài khoản {$targetUser->name}. Người dùng này sẽ không thể đăng nhập.");
        } else {
            $targetUser->update(['status' => 'active']);
            session()->flash('message', "Đã mở khóa tài khoản {$targetUser->name} thành công.");
        }
    }

    public function deleteUser(int $userId): void
    {
        $this->authorizeManage();

        $targetUser = User::findOrFail($userId);

        // Chặn tự xóa
        if ($targetUser->id === auth()->id()) {
            session()->flash('error', 'Bạn không thể tự xóa tài khoản của chính mình.');

            return;
        }

        // Chặn xóa admin cuối cùng
        if ($this->isLastAdmin($targetUser)) {
            session()->flash('error', 'Không thể xóa tài khoản quản trị viên duy nhất còn lại của hệ thống.');

            return;
        }

        $targetUserEmail = $targetUser->email;
        $targetUserId = $targetUser->id;

        // CHỈ dùng SoftDeletes để bảo toàn quan hệ audit_logs
        $targetUser->delete();

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $targetUserId)->delete();
        }

        session()->flash('message', "Đã xóa mềm người dùng {$targetUserEmail} thành công.");
    }

    public function render()
    {
        $query = User::with(['department', 'roles']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->roleFilter) {
            $query->role($this->roleFilter);
        }

        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);
        $departments = Department::orderBy('name')->get();
        $roles = Role::all();

        return view('livewire.users.user-index', [
            'users' => $users,
            'departments' => $departments,
            'roles' => $roles,
            'totalUsersCount' => User::count(),
            'activeUsersCount' => User::where('status', 'active')->count(),
            'lockedUsersCount' => User::where('status', 'locked')->count(),
        ])->layout('components.layouts.app', ['title' => 'Quản lý người dùng - MEMS']);
    }
}
