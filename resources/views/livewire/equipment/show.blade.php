<div class="space-y-6">
    <!-- Top Action Bar & Back Button -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('equipment.index') }}" class="p-2 bg-slate-900 border border-slate-800 text-slate-400 hover:text-white rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-white tracking-wide">{{ $equipment->name }}</h1>
                    <span class="font-mono text-xs px-2.5 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded-lg font-semibold">{{ $equipment->code }}</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Khoa: <strong class="text-slate-200">{{ $equipment->department?->name }}</strong> | Serial: 
                    @if($equipment->serial_number)
                        <code class="text-slate-300 font-mono">{{ $equipment->serial_number }}</code>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">⚠️ Chưa có serial</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('view', $equipment)
            <a href="{{ route('equipment.label', $equipment->id) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>In nhãn QR</span>
            </a>
            @endcan

            @can('export', App\Models\Equipment::class)
            <button wire:click="exportPdf" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Xuất hồ sơ PDF</span>
            </button>
            @endcan

            @can('update', $equipment)
            <a href="{{ route('equipment.edit', $equipment->id) }}" class="px-4 py-2 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/20 text-xs font-semibold rounded-xl transition">
                Chỉnh sửa
            </a>
            @endcan

            @can('requestDelete', $equipment)
                @if(!$equipment->delete_requested_at)
                <button wire:click="requestDelete" wire:confirm="Bạn có chắc chắn muốn gửi đề nghị xóa thiết bị này không?" class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 text-xs font-semibold rounded-xl transition">
                    ⚠️ Đề nghị xóa
                </button>
                @endif
            @endcan

            @can('delete', $equipment)
                @if($equipment->delete_requested_at)
                <button wire:click="cancelDeleteRequest" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-semibold rounded-xl transition">
                    Từ chối đề nghị xóa
                </button>
                @endif
                <button wire:click="softDelete" wire:confirm="XÁC NHẬN: Bạn có chắc chắn muốn XÓA (Soft Delete) thiết bị này không?" class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-semibold rounded-xl transition">
                    🗑️ Xóa thiết bị
                </button>
            @endcan
        </div>
    </div>

    @if(session()->has('message'))
    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    <!-- Main Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 text-xs font-semibold">
        <button wire:click="$set('activeTab', 'overview')" class="px-4 py-3 border-b-2 transition {{ $activeTab === 'overview' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Thông Tin Chi Tiết & Tình Trạng
        </button>
        <button wire:click="$set('activeTab', 'documents')" class="px-4 py-3 border-b-2 transition flex items-center gap-2 {{ $activeTab === 'documents' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            <span>Checklist Hồ Sơ Giấy Tờ</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-cyan-400 border border-cyan-500/20 font-bold">
                {{ count($docMap) }}/{{ count($allDocTypes) }}
            </span>
        </button>
    </div>

    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Asset Main Information Card -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-3">Thông tin tổng quan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-1">Tên thiết bị</span>
                        <span class="font-semibold text-white text-sm">{{ $equipment->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Mã quản lý thiết bị</span>
                        <span class="font-mono text-cyan-400 font-bold text-sm">{{ $equipment->code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Model sản phẩm</span>
                        <span class="font-medium text-slate-200">{{ $equipment->equipmentModel?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Số Serial</span>
                        @if($equipment->serial_number)
                            <span class="font-mono text-slate-200 font-semibold">{{ $equipment->serial_number }}</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">⚠️ Chưa có serial</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Loại sở hữu</span>
                        <span class="font-medium text-slate-200">{{ $equipment->ownership_type?->label() ?? 'Mua sắm' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Loại thiết bị</span>
                        <span class="font-medium text-slate-200">{{ $equipment->category?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Hãng sản xuất</span>
                        <span class="font-medium text-slate-200">{{ $equipment->manufacturer?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Nước sản xuất</span>
                        <span class="text-slate-200">{{ $equipment->origin_country ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Năm sản xuất</span>
                        <span class="text-slate-200">{{ $equipment->manufacture_year ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial & Warranty Details -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-3">Tài chính & Bảo hành</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-1">Giá trị thiết bị</span>
                        <span class="font-mono font-bold text-emerald-400 text-sm">{{ number_format($equipment->purchase_price) }} VNĐ</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Nguồn kinh phí</span>
                        <span class="text-slate-200 font-medium">{{ $equipment->funding_source ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Nhà cung cấp</span>
                        <span class="text-slate-200 font-medium">{{ $equipment->supplier?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Hạn bảo hành</span>
                        <span class="text-slate-200 font-medium">{{ $equipment->warranty_expiration_date?->format('d/m/Y') ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Ngày nhập kho</span>
                        <span class="text-slate-200">{{ $equipment->import_date?->format('d/m/Y') ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Ngày đưa vào sử dụng</span>
                        <span class="text-slate-200">{{ $equipment->usage_start_date?->format('d/m/Y') ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Condition History Section -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400 mb-4">Lịch sử Tình trạng</h3>
                @forelse($equipment->conditionHistories as $history)
                    <div class="flex items-start gap-3 pb-4 mb-4 border-b border-slate-800/60 last:border-0 last:mb-0 last:pb-0">
                        <div class="flex-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded border text-[11px] font-semibold {{ $history->old_condition?->badgeClass() }}">{{ $history->old_condition?->label() ?? 'Mới tạo' }}</span>
                                <span class="text-slate-500">→</span>
                                <span class="px-2 py-0.5 rounded border text-[11px] font-semibold {{ $history->new_condition->badgeClass() }}">{{ $history->new_condition->label() }}</span>
                            </div>
                            <p class="text-slate-300 font-medium mt-1.5">{{ $history->reason }}</p>
                            @if($history->note)
                                <p class="text-slate-400 mt-0.5">{{ $history->note }}</p>
                            @endif
                            <p class="text-slate-500 text-[11px] mt-1.5">{{ $history->checkedBy?->name ?? 'Hệ thống' }} · {{ $history->checked_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Chưa có lịch sử thay đổi tình trạng.</p>
                @endforelse
            </div>
        </div>

        <!-- Sidebar (1 col) -->
        <div class="space-y-6">
            <!-- Current Status Card -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-3">Trạng thái & Quản lý</h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-1">Khoa / Phòng quản lý</span>
                        <span class="font-bold text-white text-sm">📍 {{ $equipment->department?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Vị trí lắp đặt chi tiết</span>
                        <span class="text-slate-200 font-medium">{{ $equipment->location?->name ?? 'Chưa rõ vị trí' }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-800/80">
                        <span class="text-slate-400 block mb-1">Tình trạng kỹ thuật</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $equipment->condition?->badgeClass() }}">
                            {{ $equipment->condition?->label() }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Trạng thái sử dụng</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $equipment->status?->badgeClass() }}">
                            {{ $equipment->status?->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Condition Update -->
            @can('updateCondition', $equipment)
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400">Cập nhật Tình trạng Kỹ thuật</h3>
                
                <form wire:submit="updateCondition" class="space-y-3 text-xs">
                    <div>
                        <label class="block text-slate-400 mb-1">Chọn tình trạng mới</label>
                        <select wire:model="newCondition" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                            @foreach($conditions as $cond)
                                <option value="{{ $cond->value }}">{{ $cond->label() }}</option>
                            @endforeach
                        </select>
                        @error('newCondition') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1">Lý do thay đổi <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model="conditionReason" placeholder="VD: Phát hiện lỗi trong kiểm tra định kỳ" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200">
                        @error('conditionReason') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1">Ghi chú thêm</label>
                        <textarea wire:model="conditionNote" rows="2" placeholder="Ghi chú bổ sung..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 border border-cyan-500/30 font-semibold rounded-xl transition">
                        Cập nhật tình trạng & Lưu lịch sử
                    </button>
                </form>
            </div>
            @endcan
        </div>
    </div>

    @elseif($activeTab === 'documents')
    <!-- Documents Checklist Tab -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-bold text-white">Checklist Hồ sơ & Giấy tờ Thiết bị</h3>
                <p class="text-xs text-slate-400 mt-0.5">Theo dõi chi tiết trạng thái từng loại hồ sơ và cảnh báo hồ sơ chưa rà soát</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">STT</th>
                        <th class="px-4 py-3">Loại hồ sơ / Giấy tờ</th>
                        <th class="px-4 py-3">Trạng thái hồ sơ</th>
                        <th class="px-4 py-3">Ngày / Hạn hiệu lực</th>
                        <th class="px-4 py-3">Ghi chú</th>
                        @can('update', $equipment)
                        <th class="px-4 py-3 text-right">Thao tác</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach($allDocTypes as $index => $docType)
                        @php
                            $doc = $docMap[$docType->value] ?? null;
                            $statusEnum = $doc ? $doc->status : \App\Enums\DocumentStatusEnum::CHUA_RA_SOAT;
                            $isUnreviewed = ($statusEnum === \App\Enums\DocumentStatusEnum::CHUA_RA_SOAT);
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition {{ $isUnreviewed ? 'bg-amber-500/5' : '' }}">
                            <td class="px-4 py-3 font-mono text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-white">
                                {{ $docType->label() }}
                                @if($isUnreviewed)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        ⚠️ CHƯA RÀ SOÁT
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusEnum->badgeClass() }}">
                                    {{ $statusEnum->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-300">
                                {{ $doc?->valid_until?->format('d/m/Y') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{ $doc?->note ?? 'Chưa có ghi chú' }}
                            </td>
                            @can('update', $equipment)
                            <td class="px-4 py-3 text-right">
                                <button wire:click="openEditDocument('{{ $docType->value }}')" class="px-3 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-semibold transition">
                                    Sửa trạng thái
                                </button>
                            </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Document Edit Modal -->
    @if($showDocModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white">
                    Cập nhật Hồ sơ: {{ \App\Enums\DocumentTypeEnum::tryFrom($editingDocType)?->label() }}
                </h3>
                <button wire:click="$set('showDocModal', false)" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form wire:submit="saveDocument" class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Trạng thái hồ sơ <span class="text-rose-400">*</span></label>
                    <select wire:model="docStatus" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-semibold">
                        @foreach($docStatuses as $st)
                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                        @endforeach
                    </select>
                    @error('docStatus') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Ngày / Hạn hiệu lực (nếu có)</label>
                    <input type="date" wire:model="docValidUntil" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    @error('docValidUntil') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Ghi chú bổ sung</label>
                    <textarea wire:model="docNote" rows="3" placeholder="Nhập ghi chú hồ sơ..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                    @error('docNote') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showDocModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 hover:bg-slate-700 rounded-xl font-semibold">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl">Lưu hồ sơ</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
