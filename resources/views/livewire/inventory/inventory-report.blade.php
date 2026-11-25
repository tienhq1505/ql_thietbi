<div class="space-y-6">
    <!-- Breadcrumb & Top Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5 text-xs font-semibold text-slate-500">
                <a href="{{ route('inventory.index') }}" class="hover:text-blue-700 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kỳ kiểm kê
                </a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('inventory.show', $session->id) }}" class="hover:text-blue-700 transition">
                    {{ $session->name }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-blue-700">Báo cáo đối soát</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Báo Cáo Đối Soát Kiểm Kê
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $session->status->badgeClass() }}">
                    {{ $session->status->label() }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span>📍 Phạm vi: <strong class="text-slate-800">{{ $session->scope_name }}</strong></span>
                <span>📅 Thời gian: <strong class="text-slate-800">{{ $session->start_date?->format('d/m/Y') }} - {{ $session->end_date?->format('d/m/Y') }}</strong></span>
                <span>👤 Người lập: <strong class="text-slate-800">{{ $session->creator?->name }}</strong></span>
                @if($session->completed_at)
                    <span>🔒 Chốt lúc: <strong class="text-emerald-700">{{ $session->completed_at->format('H:i d/m/Y') }}</strong></span>
                @else
                    <span>⏳ Trạng thái: <strong class="text-amber-700">Chưa chốt</strong></span>
                @endif
            </p>
        </div>

        <!-- Action Export Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventory.show', $session->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Quay lại kiểm đếm
            </a>

            <a href="{{ route('inventory.export-excel', $session->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Xuất Excel Chênh Lệch
            </a>

            <a href="{{ route('inventory.export-pdf', $session->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Xuất Biên Bản PDF
            </a>
        </div>
    </div>

    <!-- Status Notice Banner -->
    @if(!$session->isCompleted())
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="font-bold uppercase tracking-wide text-amber-800">KỲ KIỂM KÊ CHƯA CHỐT (ĐANG THỰC HIỆN):</strong>
                Biên bản kiểm kê và báo cáo đối soát xuất tại thời điểm này là <strong>BẢN DỰ THẢO</strong>. Mọi thiết bị chưa kiểm kê sẽ hiển thị là "Chưa kiểm". Chỉ khi người quản lý chốt kỳ kiểm kê, toàn bộ thiết bị chưa quét mới tự động chuyển thành "Thiếu" và phát hành biên bản chính thức.
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong class="font-bold uppercase tracking-wide text-emerald-800">BIÊN BẢN KIỂM KÊ CHÍNH THỨC:</strong>
                Kỳ kiểm kê đã được chốt và khóa số liệu vào lúc <strong>{{ $session->completed_at?->format('H:i d/m/Y') }}</strong>. Biên bản PDF xuất ra là biên bản đối soát chính thức dùng cho báo cáo tài sản bệnh viện.
            </div>
        </div>
    @endif

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-slate-500 text-xs font-semibold block">Tổng thiết bị</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $totalCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">Theo danh mục sổ sách</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-emerald-600 text-xs font-bold block flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Khớp
            </span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $matchedCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ $totalCount > 0 ? round(($matchedCount / $totalCount) * 100, 1) : 0 }}% tổng số</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-rose-600 text-xs font-bold block flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Thiếu
            </span>
            <span class="text-2xl font-black text-rose-600 mt-1 block">{{ $missingCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ $totalCount > 0 ? round(($missingCount / $totalCount) * 100, 1) : 0 }}% tổng số</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-purple-600 text-xs font-bold block flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span> Thừa
            </span>
            <span class="text-2xl font-black text-purple-600 mt-1 block">{{ $excessCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">Ngoài danh mục</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-amber-600 text-xs font-bold block flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Hỏng
            </span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $brokenCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">Cần sửa chữa</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-slate-500 text-xs font-semibold block flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-slate-300"></span> Chưa kiểm
            </span>
            <span class="text-2xl font-black text-slate-700 mt-1 block">{{ $untestedCount }}</span>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ $totalCount > 0 ? round(($untestedCount / $totalCount) * 100, 1) : 0 }}% chưa kiểm</span>
        </div>
    </div>

    <!-- Progress Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-slate-600 font-medium">
                Tiến độ thực tế: <strong class="text-slate-900">{{ $checkedTotal }}/{{ $totalCount }}</strong> thiết bị đã được đối soát
            </span>
            <span class="font-bold text-blue-700">{{ $progressPct }}% Hoàn tất</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-emerald-500 h-2.5 rounded-full transition-all duration-300" style="width: {{ min(100, $progressPct) }}%"></div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:w-80 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo mã máy, tên, serial..." class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="w-full md:w-auto flex flex-wrap gap-2 items-center">
            <span class="text-xs text-slate-500 font-semibold mr-1">Bộ lọc kết quả:</span>
            <button type="button" wire:click="$set('resultFilter', '')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === '' ? 'bg-blue-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tất cả ({{ $totalCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'discrepancy')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'discrepancy' ? 'bg-rose-700 text-white shadow-sm' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}">
                Chỉ chênh lệch ({{ $missingCount + $excessCount + $brokenCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'khop')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'khop' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                Khớp ({{ $matchedCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'thieu')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'thieu' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                Thiếu ({{ $missingCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'thua')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'thua' ? 'bg-purple-600 text-white shadow-sm' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                Thừa ({{ $excessCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'hong')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'hong' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                Hỏng ({{ $brokenCount }})
            </button>
            <button type="button" wire:click="$set('resultFilter', 'untested')" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $resultFilter === 'untested' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Chưa kiểm ({{ $untestedCount }})
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-3.5 text-center w-12">STT</th>
                        <th class="py-3 px-3.5">Mã thiết bị</th>
                        <th class="py-3 px-3.5">Tên thiết bị & Model</th>
                        <th class="py-3 px-3.5">Khoa phòng</th>
                        <th class="py-3 px-3.5">Vị trí (Sổ sách / Thực tế)</th>
                        <th class="py-3 px-3.5 text-center">Kết quả đối soát</th>
                        <th class="py-3 px-3.5">Ghi chú / Tình trạng</th>
                        <th class="py-3 px-3.5">Người kiểm & Lúc</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $index => $item)
                        @php
                            $eq = $item->equipment;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3.5 text-center text-slate-400 font-semibold">
                                {{ $items->firstItem() + $index }}
                            </td>

                            <td class="py-3 px-3.5">
                                <span class="font-bold text-slate-900 block">{{ $item->scanned_code ?: ($eq?->code ?? '-') }}</span>
                                @if($eq && $eq->code !== $item->scanned_code)
                                    <span class="text-[10px] text-slate-400">Mã gốc: {{ $eq->code }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-3.5">
                                <div class="font-semibold text-slate-800">{{ $eq?->name ?? 'Thiết bị không xác định' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $eq?->equipmentModel?->name ?? ($eq?->equipmentModel?->code ?? '-') }}
                                    @if($eq?->serial_number)
                                        <span class="text-slate-400">• SN: {{ $eq->serial_number }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-3.5">
                                <span class="text-slate-700 font-medium">{{ $item->systemDepartment?->name ?? ($eq?->department?->name ?? '-') }}</span>
                            </td>

                            <td class="py-3 px-3.5">
                                <div class="text-slate-600">
                                    <span class="text-[10px] text-slate-400">Sổ sách:</span>
                                    {{ $item->systemLocation?->name ?? ($eq?->location?->name ?? '-') }}
                                </div>
                                @if($item->actual_location)
                                    <div class="text-blue-700 font-medium text-[11px] mt-0.5">
                                        <span class="text-[10px] text-blue-400">Thực tế:</span>
                                        {{ $item->actual_location }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 px-3.5 text-center">
                                @if($item->result)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold {{ $item->result->badgeClass() }}">
                                        {{ $item->result->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-500">
                                        Chưa kiểm
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-3.5 text-slate-600 max-w-xs truncate">
                                {{ $item->note ?: '-' }}
                            </td>

                            <td class="py-3 px-3.5 text-slate-500 text-[11px]">
                                @if($item->checker)
                                    <div class="font-medium text-slate-700">{{ $item->checker->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->checked_at?->format('H:i d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Không tìm thấy thiết bị nào phù hợp với điều kiện tìm kiếm/bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
