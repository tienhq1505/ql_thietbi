<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                Nhật Ký Hệ Thống (Audit Log)
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Theo dõi toàn bộ lịch sử tạo, sửa, xóa dữ liệu quan trọng theo cơ chế <span class="text-cyan-400 font-medium">Append-only</span> (chống sửa/xóa nhật ký).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Bảo vệ CSDL SQLite Trigger Active
            </span>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Filter by User -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Người thực hiện</label>
                <select wire:model.live="user_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <option value="">-- Tất cả người dùng --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Action -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hành động</label>
                <select wire:model.live="action" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <option value="">-- Tất cả hành động --</option>
                    <option value="created">Tạo mới (Created)</option>
                    <option value="updated">Cập nhật (Updated)</option>
                    <option value="deleted">Xóa (Deleted)</option>
                </select>
            </div>

            <!-- Filter by Auditable Type -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Loại đối tượng</label>
                <select wire:model.live="auditable_type" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <option value="">-- Tất cả đối tượng --</option>
                    @foreach($availableTypes as $class => $label)
                        <option value="{{ $class }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Từ ngày</label>
                <input type="date" wire:model.live="date_from" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Đến ngày</label>
                <input type="date" wire:model.live="date_to" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
            </div>
        </div>

        @if($user_id || $action || $auditable_type || $date_from || $date_to)
            <div class="flex justify-end pt-2 border-t border-slate-800/60">
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-300 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Xóa bộ lọc
                </button>
            </div>
        @endif
    </div>

    <!-- Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                        <th class="p-4">Thời gian</th>
                        <th class="p-4">Người thực hiện</th>
                        <th class="p-4 text-center">Hành động</th>
                        <th class="p-4">Đối tượng</th>
                        <th class="p-4">Địa chỉ IP</th>
                        <th class="p-4 text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-4 text-slate-300 text-xs font-mono whitespace-nowrap">
                                {{ $log->created_at->format('H:i:s d/m/Y') }}
                            </td>
                            <td class="p-4">
                                @if($log->user)
                                    <div class="font-semibold text-white">{{ $log->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-xs text-slate-500 italic">Hệ thống / Console</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($log->action === 'created')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Tạo mới
                                    </span>
                                @elseif($log->action === 'updated')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Cập nhật
                                    </span>
                                @elseif($log->action === 'deleted')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Xóa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-700 text-slate-300">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-cyan-300">{{ $log->auditable_name }}</span>
                                <span class="text-xs text-slate-500 font-mono ml-1">#{{ $log->auditable_id }}</span>
                            </td>
                            <td class="p-4 text-xs font-mono text-slate-400 whitespace-nowrap">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="p-4 text-center">
                                <button wire:click="viewDetails({{ $log->id }})" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 border border-cyan-500/20 text-xs font-semibold transition">
                                    Xem thay đổi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                Không tìm thấy bản ghi nhật ký nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-950/60 border-t border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Modal Xem Chi tiết Log -->
    @if($showModal && $selectedLog)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-4xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        Chi tiết Nhật Ký #{{ $selectedLog->id }}
                        <span class="text-xs px-2.5 py-0.5 rounded-lg font-semibold
                            {{ $selectedLog->action === 'created' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($selectedLog->action === 'updated' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20') }}">
                            {{ $selectedLog->action_name }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $selectedLog->auditable_name }} (ID: {{ $selectedLog->auditable_id }}) &bull;
                        {{ $selectedLog->created_at->format('H:i:s d/m/Y') }} &bull;
                        IP: {{ $selectedLog->ip_address ?? 'Không xác định' }}
                    </p>
                </div>
                <button wire:click="closeModal" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-sm">
                <!-- User summary -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="text-slate-500">Người thực hiện:</span>
                        <span class="font-semibold text-white ml-1">
                            {{ $selectedLog->user ? $selectedLog->user->name . ' (' . $selectedLog->user->email . ')' : 'Hệ thống tự động' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500">Đối tượng:</span>
                        <span class="font-mono text-cyan-300 ml-1">{{ $selectedLog->auditable_type }}</span>
                    </div>
                </div>

                @if($selectedLog->action === 'updated')
                    <!-- Comparison table -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">So sánh các trường thay đổi</h4>
                        <div class="border border-slate-800 rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 font-semibold">
                                        <th class="p-3 w-1/4">Thuộc tính</th>
                                        <th class="p-3 w-3/8 text-rose-400">Giá trị trước (Old)</th>
                                        <th class="p-3 w-3/8 text-emerald-400">Giá trị sau (New)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    @php
                                        $allKeys = array_unique(array_merge(
                                            array_keys($selectedLog->old_values ?? []),
                                            array_keys($selectedLog->new_values ?? [])
                                        ));
                                    @endphp
                                    @forelse($allKeys as $k)
                                        <tr class="hover:bg-slate-800/30">
                                            <td class="p-3 font-sans font-medium text-slate-300">{{ $k }}</td>
                                            <td class="p-3 text-rose-300/90 bg-rose-500/5 break-all">
                                                {{ is_array($selectedLog->old_values[$k] ?? null) ? json_encode($selectedLog->old_values[$k], JSON_UNESCAPED_UNICODE) : ($selectedLog->old_values[$k] ?? 'null') }}
                                            </td>
                                            <td class="p-3 text-emerald-300/90 bg-emerald-500/5 break-all">
                                                {{ is_array($selectedLog->new_values[$k] ?? null) ? json_encode($selectedLog->new_values[$k], JSON_UNESCAPED_UNICODE) : ($selectedLog->new_values[$k] ?? 'null') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="p-4 text-center text-slate-500">Không có thuộc tính thay đổi được ghi nhận.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($selectedLog->action === 'created')
                    <!-- Created values -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Dữ liệu khởi tạo (New Values)</h4>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 overflow-x-auto">
                            <pre class="text-xs font-mono text-emerald-300/90 leading-relaxed">{{ json_encode($selectedLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @elseif($selectedLog->action === 'deleted')
                    <!-- Deleted values -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-rose-400 uppercase tracking-wider">Dữ liệu trước khi xóa (Old Values)</h4>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 overflow-x-auto">
                            <pre class="text-xs font-mono text-rose-300/90 leading-relaxed">{{ json_encode($selectedLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/60 text-right">
                <button wire:click="closeModal" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition">
                    Đóng
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
