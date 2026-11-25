<div class="max-w-2xl mx-auto space-y-4 pb-12 px-2 sm:px-0">
    <!-- Top Action / Navigation Bar -->
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('equipment.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Danh sách thiết bị</span>
        </a>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <span>Thẻ quét QR</span>
        </span>
    </div>

    <!-- Notification message -->
    @if(session()->has('message'))
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- (a) Core Identification Card -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
        <!-- Tên thiết bị -->
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-snug tracking-tight">
                {{ $equipment->name }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="font-mono text-base font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-xl border border-blue-200/80">
                    {{ $equipment->code }}
                </span>
                <span class="text-xs font-medium text-slate-500">
                    {{ $equipment->category?->name ?? 'Chưa phân loại' }}
                </span>
            </div>
        </div>

        <!-- Tình trạng kỹ thuật nổi bật -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/70 flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tình trạng máy</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs sm:text-sm font-bold border {{ $equipment->condition?->badgeClass() ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                <span class="w-2 h-2 rounded-full bg-current"></span>
                <span>{{ $equipment->condition?->label() ?? 'Chưa cập nhật' }}</span>
            </span>
        </div>

        <!-- Khoa & Vị trí -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-sm">
            <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-blue-50/50 border border-blue-100/80">
                <span class="text-base">📍</span>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block">Khoa / Phòng</span>
                    <strong class="text-slate-900 font-bold block mt-0.5">{{ $equipment->department?->name ?? 'Chưa phân khoa' }}</strong>
                </div>
            </div>
            <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                <span class="text-base">📌</span>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block">Vị trí cụ thể</span>
                    <strong class="text-slate-900 font-bold block mt-0.5">{{ $equipment->location?->name ?? 'Chưa xác định vị trí' }}</strong>
                </div>
            </div>
        </div>

        <!-- Nút Đổi tình trạng to, dễ bấm cho người có quyền updateCondition -->
        @can('updateCondition', $equipment)
        <div class="pt-2">
            <button wire:click="openConditionModal" type="button" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-base shadow-lg shadow-blue-500/25 active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2 group">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Đổi Tình Trạng Thiết Bị</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- (b) Specifications & Supplier Card -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-5 shadow-sm space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
            <span>Thông số & Nhà cung cấp</span>
        </h2>
        <div class="divide-y divide-slate-100 text-sm">
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Hãng sản xuất</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->manufacturer?->name ?? '—' }}</span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Model thiết bị</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->equipmentModel?->name ?? $equipment->origin_model_text ?? '—' }}</span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Số Serial</span>
                <span class="font-mono font-semibold text-slate-900 text-right">
                    @if($equipment->serial_number)
                        {{ $equipment->serial_number }}
                    @elseif($equipment->serial_pending)
                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-xs">Chờ cập nhật</span>
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Năm sản xuất</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->manufacture_year ?? '—' }}</span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Hạn bảo hành</span>
                <span class="font-semibold text-slate-900 text-right">
                    {{ $equipment->warranty_expiration_date ? $equipment->warranty_expiration_date->format('d/m/Y') : '—' }}
                </span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Nhà cung cấp</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->supplier?->name ?? '—' }}</span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Xuất xứ</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->origin_country ?? '—' }}</span>
            </div>
            <div class="py-2.5 flex items-center justify-between gap-4">
                <span class="text-slate-500 text-xs sm:text-sm">Trạng thái quản lý</span>
                <span class="font-semibold text-slate-900 text-right">{{ $equipment->status?->label() ?? '—' }}</span>
            </div>
        </div>
    </div>

    <!-- (c) Condition History (Last 5 records) -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                <span>Lịch sử tình trạng kỹ thuật (5 lần gần nhất)</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium">{{ count($equipment->conditionHistories) }} bản ghi</span>
        </div>

        @if(count($equipment->conditionHistories) > 0)
        <div class="space-y-2.5 pt-1">
            @foreach($equipment->conditionHistories as $history)
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs space-y-1.5">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="font-medium">📅 {{ $history->checked_at ? $history->checked_at->format('H:i d/m/Y') : '—' }}</span>
                    <span>👤 {{ $history->checkedBy?->name ?? 'Hệ thống' }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="text-slate-600">{{ $history->old_condition_label }}</span>
                    <span class="text-slate-400">→</span>
                    <span class="px-2 py-0.5 rounded-lg border font-bold {{ $history->new_condition_badge_class }}">
                        {{ $history->new_condition_label }}
                    </span>
                </div>
                @if($history->reason)
                    <div class="text-slate-700 font-medium">Lý do: <span class="font-normal">{{ $history->reason }}</span></div>
                @endif
                @if($history->note)
                    <div class="text-slate-500 italic">Ghi chú: {{ $history->note }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs text-slate-400 italic py-2 text-center">Chưa có lịch sử thay đổi tình trạng.</p>
        @endif
    </div>

    <!-- (d) Legal Documents Accordion -->
    <div x-data="{ open: false }" class="bg-white border border-slate-200/90 rounded-3xl p-5 shadow-sm space-y-3">
        <button @click="open = !open" type="button" class="w-full flex items-center justify-between text-left group">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-600"></span>
                <span>Hồ sơ giấy tờ pháp lý (15 loại)</span>
            </h2>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                <span x-text="open ? 'Thu gọn' : 'Xem chi tiết'"></span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div x-show="open" x-cloak class="pt-2 divide-y divide-slate-100 text-xs">
            @foreach($allDocTypes as $docType)
                @php
                    $doc = $existingDocs->get($docType->value);
                    $status = $doc?->status;
                @endphp
                <div class="py-2.5 flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-slate-800 leading-tight">{{ $docType->label() }}</p>
                        @if($doc?->valid_until)
                            <p class="text-[11px] text-slate-400 mt-0.5">Hạn: {{ $doc->valid_until->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <div>
                        @if($status)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg border font-bold text-[11px] {{ $status->badgeClass() }}">
                                {{ $status->label() }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg border font-medium text-[11px] bg-slate-50 text-slate-400 border-slate-200">
                                Chưa rà soát
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- (e) Link to full equipment details page -->
    <div class="pt-2">
        <a href="{{ route('equipment.show', $equipment->id) }}" class="w-full py-3.5 px-4 rounded-2xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-sm shadow-sm transition flex items-center justify-center gap-2 text-center">
            <span>Xem trang hồ sơ đầy đủ</span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Modal "Đổi tình trạng" -->
    @if($showConditionModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden" @click.outside="$wire.closeConditionModal()">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Cập nhật Tình trạng Kỹ thuật</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $equipment->name }} ({{ $equipment->code }})</p>
                </div>
                <button type="button" wire:click="closeConditionModal" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    ✕
                </button>
            </div>

            <form wire:submit="saveCondition" class="p-5 space-y-4">
                <!-- Chọn tình trạng mới -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Chọn tình trạng mới <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($conditions as $cond)
                        <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition {{ $newCondition === $cond->value ? 'bg-blue-50/80 border-blue-500 ring-2 ring-blue-500/20' : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100/60' }}">
                            <input type="radio" wire:model.live="newCondition" value="{{ $cond->value }}" class="text-blue-600 focus:ring-blue-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold border {{ $cond->badgeClass() }}">
                                {{ $cond->label() }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('newCondition') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Lý do thay đổi -->
                <div>
                    <label for="conditionReason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Lý do thay đổi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="conditionReason" wire:model="conditionReason" placeholder="Ví dụ: Kiểm tra định kỳ, Báo hỏng từ khoa..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20">
                    @error('conditionReason') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Ghi chú chi tiết -->
                <div>
                    <label for="conditionNote" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Ghi chú chi tiết (nếu có)
                    </label>
                    <textarea id="conditionNote" wire:model="conditionNote" rows="2" placeholder="Ghi chú chi tiết về tình trạng, hướng xử lý..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20"></textarea>
                    @error('conditionNote') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeConditionModal" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-sm font-semibold transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/20 transition">
                        Lưu Tình Trạng
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
