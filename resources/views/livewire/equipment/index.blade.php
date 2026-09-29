<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Quản lý Trang Thiết bị Y tế</h1>
            <p class="text-sm text-slate-400 mt-1">Danh mục và danh sách thiết bị vật lý (Asset Management)</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @can('import', App\Models\Equipment::class)
            <a href="{{ route('equipment.import') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-cyan-400 border border-cyan-500/20 text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Nhập Excel</span>
            </a>
            @endcan

            @if(auth()->user() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('equipment_dept')))
            <a href="{{ route('equipment.unassigned') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Gán khoa (Chưa phân khoa)</span>
            </a>
            @endif

            @can('export', App\Models\Equipment::class)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Xuất dữ liệu</span>
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-44 bg-slate-900 border border-slate-800 rounded-xl shadow-xl z-30 py-1 text-xs">
                    <button wire:click="exportExcel" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-800 text-slate-200 flex items-center gap-2">
                        <span>📊 Xuất Excel (.xlsx)</span>
                    </button>
                    <button wire:click="exportCsv" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-800 text-slate-200 flex items-center gap-2">
                        <span>📄 Xuất CSV (.csv)</span>
                    </button>
                    <button wire:click="exportPdf" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-800 text-slate-200 flex items-center gap-2">
                        <span>📕 Xuất PDF (Tối đa 200)</span>
                    </button>
                </div>
            </div>
            @endcan

            @can('create', App\Models\Equipment::class)
            <a href="{{ route('equipment.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Thêm Thiết Bị Mới</span>
            </a>
            @endcan
        </div>
    </div>


    <!-- Filter & Search Bar Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <!-- Search Input -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Tìm kiếm</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Mã TB, tên, serial, model, hãng, khoa..." class="w-full pl-9 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Department Filter -->
            @if(!auth()->user()->hasRole('using_dept'))
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Khoa / Phòng</label>
                <select wire:model.live="department_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả Khoa --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Category Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Loại thiết bị</label>
                <select wire:model.live="category_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả loại --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Manufacturer Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Hãng sản xuất</label>
                <select wire:model.live="manufacturer_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả hãng --</option>
                    @foreach($manufacturers as $mfg)
                        <option value="{{ $mfg->id }}">{{ $mfg->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Missing Document Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-amber-400 mb-1">Thiếu hồ sơ</label>
                <select wire:model.live="missing_doc_type" class="w-full px-3 py-2 bg-slate-950 border border-amber-500/30 rounded-xl text-xs text-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    <option value="">-- Tất cả hồ sơ --</option>
                    @foreach($docTypes as $dt)
                        <option value="{{ $dt->value }}">Thiếu: {{ $dt->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Missing Serial Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-amber-400 mb-1">Số Serial</label>
                <select wire:model.live="missing_serial" class="w-full px-3 py-2 bg-slate-950 border border-amber-500/30 rounded-xl text-xs text-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    <option value="">-- Tất cả --</option>
                    <option value="1">⚠️ Chưa có Serial</option>
                </select>
            </div>

            <!-- Ownership Type Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Loại sở hữu</label>
                <select wire:model.live="ownership_type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả sở hữu --</option>
                    @foreach($ownershipTypes as $ot)
                        <option value="{{ $ot->value }}">{{ $ot->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Condition Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Tình trạng kỹ thuật</label>
                <select wire:model.live="condition" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả tình trạng --</option>
                    @foreach($conditions as $cond)
                        <option value="{{ $cond->value }}">{{ $cond->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Trạng thái sử dụng</label>
                <select wire:model.live="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="">-- Tất cả trạng thái --</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page Selector -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Số dòng/trang</label>
                <select wire:model.live="perPage" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                    <option value="10">10 dòng</option>
                    <option value="15">15 dòng</option>
                    <option value="25">25 dòng</option>
                    <option value="50">50 dòng</option>
                </select>
            </div>
        </div>

        @if($search || $department_id || $category_id || $manufacturer_id || $status || $condition || $missing_doc_type || $missing_serial || $ownership_type)
        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
            <span class="text-xs text-cyan-400 font-medium">Đang áp dụng bộ lọc nâng cao</span>
            <button wire:click="resetFilters" class="text-xs text-slate-400 hover:text-rose-400 underline transition">Xóa bộ lọc</button>
        </div>
        @endif
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('code')">
                            Mã TB
                            @if($sortField === 'code') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('name')">
                            Tên thiết bị & Model
                            @if($sortField === 'name') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 text-center">Hồ sơ đủ</th>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('department_id')">
                            Khoa / Vị trí
                            @if($sortField === 'department_id') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('manufacturer_id')">
                            Hãng & Nước SX
                            @if($sortField === 'manufacturer_id') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('condition')">
                            Tình trạng
                            @if($sortField === 'condition') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:text-white" wire:click="sortBy('status')">
                            Trạng thái
                            @if($sortField === 'status') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($equipmentList as $item)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5 font-mono font-semibold text-cyan-400">
                            <a href="{{ route('equipment.show', $item->id) }}" class="hover:underline">{{ $item->code }}</a>
                            @if($item->delete_requested_at)
                                <span class="block text-[10px] text-amber-400 font-sans mt-0.5">⚠️ Chờ duyệt xóa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-white text-sm">
                                <a href="{{ route('equipment.show', $item->id) }}" class="hover:text-cyan-300 transition">{{ $item->name }}</a>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Model: {{ $item->equipmentModel?->name }} | SN: 
                                @if($item->serial_number)
                                    <code class="font-mono text-slate-300">{{ $item->serial_number }}</code>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">⚠️ Chưa có serial</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-mono font-bold">
                            @php
                                $coCount = $item->documents->where('status', \App\Enums\DocumentStatusEnum::CO)->count();
                            @endphp
                            <span class="text-emerald-400">{{ $coCount }}</span><span class="text-slate-500">/8</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-slate-200">{{ $item->department?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->location?->name ?? 'Chưa gán vị trí' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-200">{{ $item->manufacturer?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->origin_country ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item->condition?->badgeClass() }}">
                                {{ $item->condition?->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item->status?->badgeClass() }}">
                                {{ $item->status?->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right space-x-2">
                            <a href="{{ route('equipment.show', $item->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium border border-slate-700 transition">
                                Chi tiết
                            </a>
                            @can('update', $item)
                            <a href="{{ route('equipment.edit', $item->id) }}" class="px-2.5 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg text-xs font-medium border border-cyan-500/20 transition">
                                Sửa
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-medium text-slate-300">Không tìm thấy thiết bị phù hợp</p>
                            <p class="text-xs text-slate-500 mt-1">Thử thay đổi từ khóa hoặc bộ lọc tìm kiếm.</p>
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
