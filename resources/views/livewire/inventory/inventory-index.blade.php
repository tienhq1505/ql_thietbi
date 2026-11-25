<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                Kiểm Kê Trang Thiết Bị
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Quản lý các đợt kiểm kê tài sản định kỳ, quét mã QR camera và đối soát thiết bị thực tế.
            </p>
        </div>
        @if($canCreate)
        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-sm font-bold transition shadow-lg shadow-cyan-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tạo Kỳ Kiểm Kê Mới
            </button>
        </div>
        @endif
    </div>

    <!-- Flash message -->
    @if(session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between">
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Filters Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="w-full sm:w-72">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm tên kỳ kiểm kê..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
        </div>
        <div class="w-full sm:w-auto flex flex-wrap gap-3 items-center">
            @if(!auth()->user()->hasRole('using_dept'))
            <select wire:model.live="departmentFilter" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                <option value="">-- Tất cả khoa / phòng --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            @endif

            <select wire:model.live="statusFilter" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="in_progress">Đang kiểm kê</option>
                <option value="completed">Đã chốt kỳ</option>
            </select>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($sessions as $s)
            <div class="bg-slate-900 border border-slate-800 hover:border-slate-700/80 rounded-2xl p-5 shadow-xl flex flex-col justify-between transition group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $s->status->badgeClass() }}">
                            {{ $s->status->label() }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">
                            {{ $s->start_date->format('d/m') }} - {{ $s->end_date->format('d/m/Y') }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-white group-hover:text-cyan-400 transition">
                            <a href="{{ route('inventory.show', $s->id) }}">{{ $s->name }}</a>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $s->scope_name }}
                        </p>
                    </div>

                    <!-- Progress bar -->
                    @php
                        $pct = $s->total_items > 0 ? round(($s->checked_items / $s->total_items) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1.5 pt-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Tiến độ kiểm đếm:</span>
                            <span class="font-bold text-cyan-400">{{ $s->checked_items }}/{{ $s->total_items }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-cyan-500 to-emerald-400 h-2 rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </div>

                    <!-- Quick counts -->
                    <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-800/80 text-center text-xs">
                        <div class="p-2 bg-slate-950/60 rounded-xl">
                            <span class="block text-slate-500 text-[10px]">Khớp</span>
                            <span class="font-bold text-emerald-400">{{ $s->matched_items }}</span>
                        </div>
                        <div class="p-2 bg-slate-950/60 rounded-xl">
                            <span class="block text-slate-500 text-[10px]">Thiếu</span>
                            <span class="font-bold text-rose-400">{{ $s->missing_items }}</span>
                        </div>
                        <div class="p-2 bg-slate-950/60 rounded-xl">
                            <span class="block text-slate-500 text-[10px]">Thừa</span>
                            <span class="font-bold text-purple-400">{{ $s->excess_items }}</span>
                        </div>
                        <div class="p-2 bg-slate-950/60 rounded-xl">
                            <span class="block text-slate-500 text-[10px]">Hỏng</span>
                            <span class="font-bold text-amber-400">{{ $s->broken_items }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-800/60 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500 truncate">Tạo bởi: {{ $s->creator?->name }}</span>
                    <a href="{{ route('inventory.show', $s->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-cyan-400 hover:text-cyan-300 transition">
                        <span>{{ $s->isCompleted() ? 'Xem kết quả' : 'Vào kiểm kê' }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-base font-semibold text-slate-400">Chưa có kỳ kiểm kê nào phù hợp.</p>
                <p class="text-xs text-slate-500 mt-1">Bấm "Tạo Kỳ Kiểm Kê Mới" để bắt đầu đợt kiểm kê tài sản.</p>
            </div>
        @endforelse
    </div>

    <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl">
        {{ $sessions->links() }}
    </div>

    <!-- Modal Tạo Kỳ Kiểm Kê Mới -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Khởi Tạo Kỳ Kiểm Kê Mới</h3>
                <button wire:click="closeCreateModal" class="p-1 rounded-lg text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="createSession" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tên kỳ kiểm kê <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500" placeholder="VD: Kiểm kê quý 4/2026">
                    @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phạm vi kiểm kê</label>
                    @if(auth()->user()->hasRole('using_dept'))
                        <input type="text" readonly value="{{ auth()->user()->department?->name }}" class="w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-sm text-slate-400 cursor-not-allowed">
                        <span class="text-[11px] text-slate-500 mt-1 block">Tài khoản khoa chỉ được tạo kỳ kiểm kê trong khoa của mình.</span>
                    @else
                        <select wire:model="department_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                            <option value="">-- Toàn bệnh viện (Tất cả thiết bị) --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ngày bắt đầu <span class="text-rose-400">*</span></label>
                        <input type="date" wire:model="start_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                        @error('start_date') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ngày kết thúc <span class="text-rose-400">*</span></label>
                        <input type="date" wire:model="end_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-cyan-500">
                        @error('end_date') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="p-3 bg-cyan-500/5 border border-cyan-500/20 rounded-xl text-xs text-slate-300 space-y-1">
                    <p class="font-semibold text-cyan-400">💡 Lưu ý quan trọng:</p>
                    <p>Hệ thống sẽ tự động chụp dữ liệu (snapshot) toàn bộ thiết bị đang quản lý (loại trừ thiết bị đã thanh lý) vào kỳ kiểm kê này với kết quả ban đầu là Chưa kiểm.</p>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" wire:click="closeCreateModal" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-sm font-bold transition shadow-lg shadow-cyan-500/20">
                        Khởi Tạo Kỳ
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
