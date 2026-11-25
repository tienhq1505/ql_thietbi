<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                Quản Lý Người Dùng
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Quản lý danh sách tài khoản, phân quyền 5 vai trò, gán khoa phòng và kiểm soát trạng thái đăng nhập.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Thêm Người Dùng Mới
            </button>
        </div>
    </div>

    <!-- Flash message -->
    @if(session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2 font-medium">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('message') }}
            </span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2 font-medium">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ session('error') }}
            </span>
        </div>
    @endif

    <!-- Stats summary badges -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs block font-semibold">Tổng tài khoản</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $totalUsersCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                👥
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-emerald-700 text-xs block font-semibold">Đang hoạt động</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $activeUsersCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                ✓
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-rose-700 text-xs block font-semibold">Đã bị khóa</span>
                <span class="text-2xl font-black text-rose-600 mt-1 block">{{ $lockedUsersCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                🔒
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="w-full md:w-80 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo tên hoặc email..." class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="w-full md:w-auto flex flex-wrap gap-2.5 items-center">
            <select wire:model.live="roleFilter" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Tất cả vai trò --</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ $r->display_name ?? $r->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="departmentFilter" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-xs">
                <option value="">-- Tất cả khoa / phòng --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active">Đang hoạt động</option>
                <option value="locked">Đã khóa</option>
            </select>
        </div>
    </div>

    <!-- Users Data Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-3.5 text-center w-12">STT</th>
                        <th class="py-3 px-3.5">Họ và tên / Email</th>
                        <th class="py-3 px-3.5">Vai trò hệ thống</th>
                        <th class="py-3 px-3.5">Khoa / Phòng</th>
                        <th class="py-3 px-3.5 text-center">Trạng thái</th>
                        <th class="py-3 px-3.5">Ngày tạo</th>
                        <th class="py-3 px-3.5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $index => $u)
                        @php
                            $roleName = $u->roles->first()?->name;
                            $roleBadge = match($roleName) {
                                'admin' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                'equipment_dept' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                'technician' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                'using_dept' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'directorate' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                default => 'bg-slate-100 text-slate-600 border border-slate-200',
                            };
                            $isSelf = $u->id === auth()->id();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ $u->status === 'locked' ? 'bg-slate-50/40 text-slate-400' : '' }}">
                            <td class="py-3 px-3.5 text-center text-slate-400 font-semibold">
                                {{ $users->firstItem() + $index }}
                            </td>

                            <td class="py-3 px-3.5">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    {{ $u->name }}
                                    @if($isSelf)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-700 font-semibold">(Bạn)</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $u->email }}</div>
                            </td>

                            <td class="py-3 px-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold {{ $roleBadge }}">
                                    {{ $u->roles->first()?->display_name ?? $u->roles->first()?->name ?? 'Chưa phân vai trò' }}
                                </span>
                            </td>

                            <td class="py-3 px-3.5">
                                @if($u->department)
                                    <span class="text-slate-800 font-medium">{{ $u->department->name }}</span>
                                @else
                                    <span class="text-slate-400 italic">Toàn viện (Không gán khoa)</span>
                                @endif
                            </td>

                            <td class="py-3 px-3.5 text-center">
                                @if($u->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Đã khóa
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-3.5 text-slate-500 text-[11px]">
                                {{ $u->created_at?->format('d/m/Y') }}
                            </td>

                            <td class="py-3 px-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Sửa -->
                                    <button wire:click="openEditModal({{ $u->id }})" title="Chỉnh sửa thông tin" class="p-1.5 rounded-lg text-slate-600 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <!-- Đổi mật khẩu -->
                                    <button wire:click="openResetModal({{ $u->id }})" title="Đặt lại mật khẩu" class="p-1.5 rounded-lg text-slate-600 hover:text-amber-700 hover:bg-amber-50 border border-slate-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </button>

                                    <!-- Khóa / Mở khóa -->
                                    @if($u->status === 'active')
                                        <button wire:click="toggleStatus({{ $u->id }})" wire:confirm="Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập và phiên hiện tại sẽ bị hủy!" title="Khóa tài khoản" class="p-1.5 rounded-lg text-slate-600 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition" @disabled($isSelf)>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button wire:click="toggleStatus({{ $u->id }})" title="Mở khóa tài khoản" class="p-1.5 rounded-lg text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 border border-slate-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Xóa mềm -->
                                    <button wire:click="deleteUser({{ $u->id }})" wire:confirm="Bạn có chắc chắn muốn xóa mềm người dùng này? Tài khoản sẽ bị ẩn nhưng lịch sử Audit Log vẫn được bảo toàn." title="Xóa người dùng" class="p-1.5 rounded-lg text-slate-600 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition" @disabled($isSelf)>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Không tìm thấy người dùng nào phù hợp với bộ lọc tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Create User Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Thêm Người Dùng Mới
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form wire:submit="createUser" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Họ và tên <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Ví dụ: Nguyễn Văn A" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Địa chỉ Email <span class="text-rose-500">*</span></label>
                        <input type="email" wire:model="email" placeholder="example@mems.local" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mật khẩu khởi tạo <span class="text-rose-500">*</span> (tối thiểu 8 ký tự)</label>
                        <input type="password" wire:model="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('password') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Vai trò hệ thống <span class="text-rose-500">*</span></label>
                        <select wire:model.live="role" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->display_name ?? $r->name }} ({{ $r->name }})</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Khoa / Phòng
                            @if($role === 'using_dept')
                                <span class="text-rose-500">* (Bắt buộc cho Khoa sử dụng)</span>
                            @else
                                <span class="text-slate-400 font-normal">(Tùy chọn)</span>
                            @endif
                        </label>
                        <select wire:model="department_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn khoa / phòng --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm">
                            Tạo người dùng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit User Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Chỉnh Sửa Người Dùng
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form wire:submit="updateUser" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Họ và tên <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="edit_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('edit_name') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Địa chỉ Email <span class="text-rose-500">*</span></label>
                        <input type="email" wire:model="edit_email" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('edit_email') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Vai trò hệ thống <span class="text-rose-500">*</span></label>
                        <select wire:model.live="edit_role" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->display_name ?? $r->name }} ({{ $r->name }})</option>
                            @endforeach
                        </select>
                        @error('edit_role') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Khoa / Phòng
                            @if($edit_role === 'using_dept')
                                <span class="text-rose-500">* (Bắt buộc cho Khoa sử dụng)</span>
                            @else
                                <span class="text-slate-400 font-normal">(Tùy chọn)</span>
                            @endif
                        </label>
                        <select wire:model="edit_department_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Không gán khoa / Toàn viện --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('edit_department_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm">
                            Cập nhật thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Reset Password Modal -->
    @if($showResetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Đặt Lại Mật Khẩu
                    </h3>
                    <button wire:click="$set('showResetModal', false)" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form wire:submit="resetPassword" class="p-5 space-y-4">
                    <p class="text-xs text-slate-600">
                        Đặt mật khẩu mới cho tài khoản: <strong class="text-slate-900">{{ $resetUserName }}</strong>
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mật khẩu mới <span class="text-rose-500">*</span> (tối thiểu 8 ký tự)</label>
                        <input type="password" wire:model="new_password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('new_password') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-slate-400 mt-1">Lưu ý: Mật khẩu sẽ được mã hóa và không hiển thị lại sau khi lưu.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showResetModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition shadow-sm">
                            Lưu mật khẩu mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
