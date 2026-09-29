<x-layouts.app title="Dashboard - MEMS Hospital">
    <div class="space-y-6">
        <!-- Top Bar Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 backdrop-blur border border-slate-800 p-6 rounded-3xl">
            <div>
                <h2 class="text-xl font-bold text-white">Xin chào, {{ auth()->user()->name }}! 👋</h2>
                <p class="text-xs text-slate-400 mt-1">Hệ thống Quản lý Trang thiết bị Y tế - Bệnh viện Đa khoa Phase 1 (MVP)</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    Vai trò: {{ auth()->user()->roles->first()?->display_name ?? auth()->user()->roles->first()?->name }}
                </span>
                @if(auth()->user()->department)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        📍 {{ auth()->user()->department->name }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400">Tổng khoa/phòng</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ \App\Models\Department::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center">
                    🏥
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400">Người dùng hệ thống</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ \App\Models\User::count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    👥
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400">Vai trò mặc định</p>
                    <p class="text-2xl font-bold text-white mt-1">5</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                    🛡️
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400">Trạng thái hệ thống</p>
                    <p class="text-xs font-bold text-emerald-400 mt-2 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Hoạt động tốt
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    ⚡
                </div>
            </div>
        </div>

        <!-- Role Matrix Info Card -->
        <div class="bg-slate-900/80 border border-slate-800 p-6 rounded-3xl">
            <h3 class="text-base font-bold text-white mb-4">Ma trận phân quyền Module 1</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-300">
                    <thead class="text-[11px] uppercase bg-slate-800/80 text-slate-400">
                        <tr>
                            <th class="p-3 rounded-l-xl">Vai trò</th>
                            <th class="p-3">Xem thiết bị</th>
                            <th class="p-3">Sửa thiết bị</th>
                            <th class="p-3">Xóa thiết bị</th>
                            <th class="p-3">Đổi tình trạng</th>
                            <th class="p-3">Kiểm kê</th>
                            <th class="p-3">Import/Export</th>
                            <th class="p-3 rounded-r-xl">Quản lý User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <tr class="{{ auth()->user()->hasRole('admin') ? 'bg-cyan-950/40 text-cyan-300 font-semibold' : '' }}">
                            <td class="p-3 font-medium text-white">Quản trị hệ thống</td>
                            <td class="p-3 text-emerald-400">Toàn bộ</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓ (Soft delete)</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('equipment_dept') ? 'bg-cyan-950/40 text-cyan-300 font-semibold' : '' }}">
                            <td class="p-3 font-medium text-white">Phòng Vật tư-TBYT</td>
                            <td class="p-3 text-emerald-400">Toàn bộ</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-amber-400">✗ (Chỉ đề nghị)</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-slate-500">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('technician') ? 'bg-cyan-950/40 text-cyan-300 font-semibold' : '' }}">
                            <td class="p-3 font-medium text-white">Kỹ thuật viên</td>
                            <td class="p-3 text-emerald-400">Toàn bộ</td>
                            <td class="p-3 text-amber-400">Cập nhật tình trạng</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-emerald-400">✓</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('using_dept') ? 'bg-cyan-950/40 text-cyan-300 font-semibold' : '' }}">
                            <td class="p-3 font-medium text-white">Khoa sử dụng</td>
                            <td class="p-3 text-amber-400">Chỉ khoa mình</td>
                            <td class="p-3 text-amber-400">✗ (Chỉ đề nghị)</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-emerald-400">✓ (Khoa mình)</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                        </tr>
                        <tr class="{{ auth()->user()->hasRole('directorate') ? 'bg-cyan-950/40 text-cyan-300 font-semibold' : '' }}">
                            <td class="p-3 font-medium text-white">Ban giám đốc</td>
                            <td class="p-3 text-emerald-400">Toàn bộ (Xem)</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-slate-500">✗</td>
                            <td class="p-3 text-emerald-400">Xuất báo cáo</td>
                            <td class="p-3 text-slate-500">✗</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
