<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Danh sách kỳ kiểm kê
                </a>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                {{ $session->name }}
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $session->status->badgeClass() }}">
                    {{ $session->status->label() }}
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span>📍 Phạm vi: <strong class="text-white">{{ $session->scope_name }}</strong></span>
                <span>📅 Thời gian: <strong class="text-white">{{ $session->start_date->format('d/m/Y') }} - {{ $session->end_date->format('d/m/Y') }}</strong></span>
                <span>👤 Người lập: <strong class="text-white">{{ $session->creator?->name }}</strong></span>
                @if($session->completed_at)
                    <span>🔒 Chốt lúc: <strong class="text-emerald-400">{{ $session->completed_at->format('H:i d/m/Y') }}</strong></span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventory.report', $session->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Báo Cáo Đối Soát
            </a>
            <a href="{{ route('inventory.labels', $session->id) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-sm font-bold transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                In nhãn QR
            </a>
            @if(!$session->isCompleted() && $canComplete)
            <button wire:click="completeSession" wire:confirm="Bạn có chắc chắn muốn chốt kỳ kiểm kê này? Mọi thiết bị chưa kiểm sẽ tự động chuyển thành THIẾU và kỳ kiểm kê sẽ bị khóa!" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white text-sm font-bold transition shadow-lg shadow-rose-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Chốt Kỳ Kiểm Kê
            </button>
            @endif
        </div>
    </div>

    <!-- Notification messages -->
    @if(session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between">
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-slate-400 text-xs block">Tổng thiết bị</span>
            <span class="text-xl font-bold text-white mt-0.5 block">{{ $totalCount }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-slate-400 text-xs block">Chưa kiểm</span>
            <span class="text-xl font-bold text-slate-300 mt-0.5 block">{{ $untestedCount }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-emerald-400 text-xs block">✓ Khớp</span>
            <span class="text-xl font-bold text-emerald-400 mt-0.5 block">{{ $matchedCount }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-rose-400 text-xs block">✗ Thiếu</span>
            <span class="text-xl font-bold text-rose-400 mt-0.5 block">{{ $missingCount }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-purple-400 text-xs block">+ Thừa</span>
            <span class="text-xl font-bold text-purple-400 mt-0.5 block">{{ $excessCount }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow-lg">
            <span class="text-amber-400 text-xs block">⚠ Hỏng</span>
            <span class="text-xl font-bold text-amber-400 mt-0.5 block">{{ $brokenCount }}</span>
        </div>
    </div>

    <!-- Progress bar -->
    @php
        $checkedTotal = $totalCount - $untestedCount;
        $pct = $totalCount > 0 ? round(($checkedTotal / $totalCount) * 100, 1) : 0;
    @endphp
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-slate-400">Tiến độ kiểm đếm: <strong class="text-white">{{ $checkedTotal }}/{{ $totalCount }}</strong> thiết bị</span>
            <span class="font-bold text-cyan-400">{{ $pct }}% Hoàn tất</span>
        </div>
        <div class="w-full bg-slate-950 rounded-full h-2.5 overflow-hidden border border-slate-800">
            <div class="bg-gradient-to-r from-cyan-500 to-emerald-400 h-2.5 rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
        </div>
    </div>

    <!-- Scanner & Manual Input Section (Only active when in_progress) -->
    @if(!$session->isCompleted())
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Camera Scanner (Col 5) -->
        <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4" x-data="qrScanner()">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="isScanning ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600'"></span>
                    <h3 class="text-sm font-bold text-white">Quét Mã QR Camera</h3>
                </div>
                <button type="button" @click="toggleCamera" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition" :class="isScanning ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'">
                    <span x-text="isScanning ? 'Tắt Camera' : 'Bật Camera'"></span>
                </button>
            </div>

            <!-- Scanner Box (wire:ignore to prevent re-rendering by Livewire) -->
            <div class="relative">
                <div wire:ignore id="qr-reader" class="w-full aspect-video sm:aspect-square bg-slate-950 rounded-xl overflow-hidden border border-slate-800 flex items-center justify-center text-xs text-slate-500">
                    <span x-show="!isScanning">Nhấn "Bật Camera" để quét nhãn QR trên thiết bị.</span>
                </div>
            </div>

            <div x-show="errorMessage" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-400">
                <span x-text="errorMessage"></span>
            </div>
            <p class="text-[11px] text-slate-500">
                💡 Lưu ý: Trình duyệt yêu cầu môi trường <strong>localhost</strong> hoặc kết nối bảo mật <strong>HTTPS</strong> để cấp quyền mở Camera.
            </p>
        </div>

        <!-- Manual Code Input & Quick Action (Col 7) -->
        <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4 flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Nhập Mã Thiết Bị Thủ Công
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Nhập mã quản lý thiết bị (VD: TB-xxx-yy) khi không dùng camera.</p>
                </div>

                <!-- Scan Feedback Messages -->
                @if($scanSuccessMessage || session()->has('scan_success'))
                    <div class="mt-4 p-3.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ $scanSuccessMessage ?? session('scan_success') }}</span>
                    </div>
                @endif

                @if($scanWarningMessage || session()->has('scan_warning'))
                    <div class="mt-4 p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $scanWarningMessage ?? session('scan_warning') }}</span>
                    </div>
                @endif

                @if($scanErrorMessage || session()->has('scan_error'))
                    <div class="mt-4 p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-400 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ $scanErrorMessage ?? session('scan_error') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="submitManualCode" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Mã thiết bị (Quét QR hoặc gõ mã) <span class="text-cyan-400">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="manualCode" placeholder="Nhập mã thiết bị rồi ấn Enter..." autofocus class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono placeholder:font-sans focus:outline-none focus:border-cyan-500">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-sm font-bold transition shadow-lg shadow-cyan-500/20 whitespace-nowrap">
                                Xác Nhận
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Vị trí thực tế ghi nhận (tùy chọn)</label>
                            <input type="text" wire:model="actualLocation" placeholder="VD: Phòng Cấp cứu 1" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Ghi chú kiểm kê (tùy chọn)</label>
                            <input type="text" wire:model="scanNote" placeholder="VD: Máy hoạt động tốt, đủ phụ kiện" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 text-[11px] text-slate-400">
                <strong>Quy tắc quét:</strong> Thiết bị trong danh sách kỳ sẽ chuyển thành <span class="text-emerald-400 font-semibold">Khớp</span>. Thiết bị phát hiện ngoài kế hoạch sẽ ghi nhận thành <span class="text-purple-400 font-semibold">Thừa</span>. Nếu phát hiện máy hư hỏng, bấm nút "Báo hỏng" bên dưới bảng.
            </div>
        </div>
    </div>
    @else
    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg">
                ✓
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Kỳ kiểm kê này đã được CHỐT HOÀN TẤT</h3>
                <p class="text-xs text-slate-400 mt-0.5">Dữ liệu đã được khóa theo cơ chế an toàn. Mọi thiết bị không kiểm đếm được ghi nhận là THIẾU.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Items Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl space-y-4">
        <!-- Table Search & Filters -->
        <div class="p-4 border-b border-slate-800 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="w-full sm:w-80">
                <input type="text" wire:model.live.debounce.300ms="searchItem" placeholder="Lọc theo mã, tên thiết bị, serial..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
            </div>
            <div class="w-full sm:w-auto flex items-center gap-3">
                <select wire:model.live="resultFilter" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                    <option value="">-- Tất cả kết quả --</option>
                    <option value="untested">Chưa kiểm</option>
                    <option value="khop">Khớp</option>
                    <option value="thieu">Thiếu</option>
                    <option value="thua">Thừa</option>
                    <option value="hong">Hỏng</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                        <th class="p-4">Mã Thiết Bị</th>
                        <th class="p-4">Tên Thiết Bị / Serial</th>
                        <th class="p-4">Khoa / Vị trí hệ thống</th>
                        <th class="p-4">Vị trí thực tế</th>
                        <th class="p-4 text-center">Kết quả</th>
                        <th class="p-4">Ghi chú</th>
                        <th class="p-4">Người kiểm</th>
                        <th class="p-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-4 font-mono font-bold text-cyan-400 whitespace-nowrap">
                                {{ $item->scanned_code ?? $item->equipment?->code }}
                            </td>
                            <td class="p-4">
                                @if($item->equipment)
                                    <div class="font-semibold text-white">{{ $item->equipment->name }}</div>
                                    <div class="text-xs text-slate-400">
                                        SN: {{ $item->equipment->serial_number ?? '—' }} &bull; {{ $item->equipment->category?->name }}
                                    </div>
                                @else
                                    <span class="text-slate-500 italic">Thiết bị không xác định</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                <div>{{ $item->systemDepartment?->name ?? '—' }}</div>
                                <div class="text-slate-500">{{ $item->systemLocation?->name ?? '' }}</div>
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                {{ $item->actual_location ?? '—' }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($item->result)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $item->result->badgeClass() }}">
                                        {{ $item->result->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700/60">
                                        Chưa kiểm
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-xs text-slate-400 max-w-xs truncate">
                                {{ $item->note ?? '—' }}
                            </td>
                            <td class="p-4 text-xs whitespace-nowrap">
                                @if($item->checker)
                                    <span class="font-medium text-white">{{ $item->checker->name }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $item->checked_at?->format('H:i d/m') }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if(!$session->isCompleted())
                                    <button wire:click="openBrokenModal({{ $item->id }})" class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 text-xs font-semibold transition" title="Báo thiết bị hư hỏng">
                                        Báo hỏng
                                    </button>
                                @else
                                    <span class="text-xs text-slate-600">Đã khóa</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                Không tìm thấy thiết bị nào phù hợp với bộ lọc trong kỳ kiểm kê này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-950/60 border-t border-slate-800">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Báo Hỏng Thiết Bị -->
    @if($showBrokenModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="text-amber-400">⚠</span> Báo Hỏng Thiết Bị Trong Kiểm Kê
                </h3>
                <button wire:click="closeBrokenModal" class="p-1 rounded-lg text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="confirmMarkBroken" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Mô tả tình trạng hỏng <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="brokenReason" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="VD: Màn hình không lên nguồn, cáp nguồn đứt">
                    @error('brokenReason') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ghi chú thêm (tùy chọn)</label>
                    <textarea wire:model="brokenNote" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Chi tiết linh kiện, hiện tượng hư hỏng..."></textarea>
                    @error('brokenNote') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="p-3 bg-amber-500/5 border border-amber-500/20 rounded-xl text-xs text-slate-300">
                    <p class="font-semibold text-amber-400 mb-0.5">Quy tắc nghiệp vụ:</p>
                    <p>Nếu bạn có thẩm quyền kỹ thuật (Admin / Kỹ thuật viên / Vật tư), hệ thống sẽ tự động cập nhật tình trạng thiết bị thành <strong>Hỏng</strong> và lưu vết lịch sử tình trạng. Nếu là Khoa sử dụng, hệ thống chỉ ghi nhận kết quả kiểm kê Hỏng mà không can thiệp trạng thái kỹ thuật.</p>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" wire:click="closeBrokenModal" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                        Xác Nhận Báo Hỏng
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
