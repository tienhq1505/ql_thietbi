<x-layouts.app title="Dashboard - MEMS Hospital">
    <div class="space-y-6">
        <!-- Top Bar Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200/90 p-6 rounded-2xl shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Xin chào, {{ auth()->user()->name }}! 👋</h2>
                <p class="text-xs text-slate-500 mt-1">Hệ thống Quản lý Trang thiết bị Y tế - Bệnh viện Đa khoa Phase 1 (MVP)</p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    Vai trò: {{ auth()->user()->roles->first()?->display_name ?? auth()->user()->roles->first()?->name }}
                </span>
                @if(auth()->user()->department)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        📍 {{ auth()->user()->department->name }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/90 p-5 rounded-2xl flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Tổng khoa/phòng</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\Department::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-xl">
                    🏥
                </div>
            </div>

            <div class="bg-white border border-slate-200/90 p-5 rounded-2xl flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Người dùng hệ thống</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\User::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                    👥
                </div>
            </div>

            <div class="bg-white border border-slate-200/90 p-5 rounded-2xl flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Vai trò mặc định</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">5</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                    🛡️
                </div>
            </div>

            <div class="bg-white border border-slate-200/90 p-5 rounded-2xl flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Trạng thái hệ thống</p>
                    <p class="text-xs font-bold text-emerald-600 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hoạt động tốt
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center text-xl">
                    ⚡
                </div>
            </div>
        </div>

        <!-- Role Matrix Info Card -->
        <div class="bg-white border border-slate-200/90 p-6 rounded-2xl shadow-xs">
            <h3 class="text-base font-bold text-slate-900 mb-4">Ma trận phân quyền Module 1</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-xs text-left text-slate-700">
                    <thead class="text-[11px] uppercase bg-blue-50/80 text-blue-900 border-b border-blue-200/60">
                        <tr>
                            <th class="p-3">Vai trò</th>
                            <th class="p-3">Xem thiết bị</th>
                            <th class="p-3">Sửa thiết bị</th>
                            <th class="p-3">Xóa thiết bị</th>
                            <th class="p-3">Đổi tình trạng</th>
                            <th class="p-3">Kiểm kê</th>
                            <th class="p-3">Import/Export</th>
                            <th class="p-3">Quản lý User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="{{ auth()->user()->hasRole('admin') ? 'bg-blue-50 font-semibold' : '' }}">
                            <td class="p-3 font-semibold text-slate-900">Quản trị hệ thống</td>
                            <td class="p-3 text-emerald-600 font-medium">Toàn bộ</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓ (Soft delete)</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('equipment_dept') ? 'bg-blue-50 font-semibold' : '' }}">
                            <td class="p-3 font-semibold text-slate-900">Phòng Vật tư-TBYT</td>
                            <td class="p-3 text-emerald-600 font-medium">Toàn bộ</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-amber-600 font-medium">✗ (Chỉ đề nghị)</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-slate-400">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('technician') ? 'bg-blue-50 font-semibold' : '' }}">
                            <td class="p-3 font-semibold text-slate-900">Kỹ thuật viên</td>
                            <td class="p-3 text-emerald-600 font-medium">Toàn bộ</td>
                            <td class="p-3 text-amber-600 font-medium">Cập nhật tình trạng</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-emerald-600 font-medium">✓</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('using_dept') ? 'bg-blue-50 font-semibold' : '' }}">
                            <td class="p-3 font-semibold text-slate-900">Khoa sử dụng</td>
                            <td class="p-3 text-amber-600 font-medium">Chỉ khoa mình</td>
                            <td class="p-3 text-amber-600 font-medium">✗ (Chỉ đề nghị)</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-emerald-600 font-medium">✓ (Khoa mình)</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('directorate') ? 'bg-blue-50 font-semibold' : '' }}">
                            <td class="p-3 font-semibold text-slate-900">Ban giám đốc</td>
                            <td class="p-3 text-emerald-600 font-medium">Toàn bộ (Xem)</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-slate-400">✗</td>
                            <td class="p-3 text-emerald-600 font-medium">Xuất báo cáo</td>
                            <td class="p-3 text-slate-400">✗</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
