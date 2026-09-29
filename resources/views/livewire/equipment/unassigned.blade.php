<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Gán khoa cho Thiết bị Chưa phân khoa</h1>
            <p class="text-sm text-slate-400 mt-1">Liệt kê các thiết bị thuộc khoa "Chưa phân khoa" và cho phép gán khoa làm việc hàng loạt</p>
        </div>
        <div>
            <a href="{{ route('equipment.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Quay lại danh sách thiết bị</span>
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Control & Bulk Assignment Panel -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-xl">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-end justify-between gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Tìm kiếm thiết bị</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Mã TB, tên thiết bị, serial, model..." class="w-full pl-9 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Bulk Assign Form -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 bg-slate-950/60 p-3 rounded-xl border border-slate-800">
                <div class="sm:w-64">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-cyan-400 mb-1">
                        Chọn Khoa / Phòng cần gán
                    </label>
                    <select wire:model="target_department_id" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        <option value="">-- Chọn Khoa đích --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('target_department_id')
                        <span class="block text-[10px] text-rose-400 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <button wire:click="assignDepartment" 
                        wire:confirm="Bạn có chắc chắn muốn gán {{ count($selectedEquipment) }} thiết bị đã chọn vào Khoa / Phòng này không?"
                        @if(empty($selectedEquipment)) disabled @endif
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition shadow-lg shadow-cyan-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Gán Khoa ({{ count($selectedEquipment) }})</span>
                </button>
            </div>
        </div>

        @error('selectedEquipment')
            <div class="text-xs text-rose-400 font-medium">{{ $message }}</div>
        @enderror
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-700 bg-slate-900 text-cyan-500 focus:ring-cyan-500">
                        </th>
                        <th class="px-4 py-3">Mã TB</th>
                        <th class="px-4 py-3">Tên thiết bị & Model</th>
                        <th class="px-4 py-3">Hãng & Nước SX</th>
                        <th class="px-4 py-3">Năm SX</th>
                        <th class="px-4 py-3">Khoa hiện tại</th>
                        <th class="px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($equipmentList as $item)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5 text-center">
                            <input type="checkbox" wire:model.live="selectedEquipment" value="{{ $item->id }}" class="rounded border-slate-700 bg-slate-900 text-cyan-500 focus:ring-cyan-500">
                        </td>
                        <td class="px-4 py-3.5 font-mono font-semibold text-cyan-400">
                            <a href="{{ route('equipment.show', $item->id) }}" class="hover:underline" target="_blank">{{ $item->code }}</a>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-white text-sm">
                                <a href="{{ route('equipment.show', $item->id) }}" class="hover:text-cyan-300 transition" target="_blank">{{ $item->name }}</a>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Model: {{ $item->equipmentModel?->name }} | Serial: 
                                @if($item->serial_number)
                                    <code class="font-mono text-slate-300">{{ $item->serial_number }}</code>
                                @else
                                    <span class="text-amber-400">⚠️ Chưa có serial</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-200">{{ $item->manufacturer?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->origin_country ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            {{ $item->manufacture_year ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                {{ $item->department?->name ?? 'Chưa phân khoa' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('equipment.show', $item->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium border border-slate-700 transition" target="_blank">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-medium text-slate-300">Không có thiết bị nào thuộc "Chưa phân khoa"</p>
                            <p class="text-xs text-slate-500 mt-1">Tất cả thiết bị đã được gán vào các khoa phòng làm việc.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($equipmentList->hasPages())
        <div class="px-4 py-3 bg-slate-950/80 border-t border-slate-800">
            {{ $equipmentList->links() }}
        </div>
        @endif
    </div>
</div>
