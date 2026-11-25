<div class="space-y-6">
    @if(session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif
    @if(session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-sm font-medium">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                Nhập Dữ liệu Thiết bị từ Excel
            </h1>
            <p class="text-sm text-slate-400 mt-1">Hỗ trợ tải file Excel chuẩn, kiểm tra dữ liệu 2 bước trước khi lưu vào cơ sở dữ liệu.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="downloadSample" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold border border-slate-700 transition shadow-sm">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Tải File Mẫu Excel
            </button>
            <a href="/equipment" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white text-sm font-medium border border-slate-800 transition">
                Quay lại Danh sách
            </a>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="flex items-center gap-3 p-3 rounded-xl transition {{ $step === 'upload' ? 'bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold' : 'text-slate-500' }}">
                <span class="w-8 h-8 rounded-full flex items-center justify-center border text-sm font-bold {{ $step === 'upload' ? 'bg-cyan-500 text-slate-950 border-cyan-400' : 'border-slate-700 bg-slate-800 text-slate-400' }}">1</span>
                <span class="text-sm">Bước 1: Chọn File Excel</span>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl transition {{ $step === 'preview' ? 'bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold' : 'text-slate-500' }}">
                <span class="w-8 h-8 rounded-full flex items-center justify-center border text-sm font-bold {{ $step === 'preview' ? 'bg-cyan-500 text-slate-950 border-cyan-400' : 'border-slate-700 bg-slate-800 text-slate-400' }}">2</span>
                <span class="text-sm">Bước 2: Xem trước & Kiểm tra</span>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl transition {{ $step === 'done' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold' : 'text-slate-500' }}">
                <span class="w-8 h-8 rounded-full flex items-center justify-center border text-sm font-bold {{ $step === 'done' ? 'bg-emerald-500 text-slate-950 border-emerald-400' : 'border-slate-700 bg-slate-800 text-slate-400' }}">3</span>
                <span class="text-sm">Bước 3: Hoàn tất</span>
            </div>
        </div>
    </div>

    <!-- BƯỚC 1: UPLOAD -->
    @if($step === 'upload')
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-2xl mx-auto space-y-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>

        <div>
            <h3 class="text-lg font-bold text-white">Tải lên file danh sách thiết bị (.xlsx, .xls)</h3>
            <p class="text-sm text-slate-400 mt-1">Dung lượng tối đa 5MB, số lượng thiết bị tối đa 500 dòng/file.</p>
        </div>

        <form wire:submit.prevent="uploadAndPreview" class="space-y-6">
            <div class="border-2 border-dashed border-slate-700 hover:border-cyan-500/50 rounded-2xl p-8 transition bg-slate-950/50 cursor-pointer relative">
                <input type="file" wire:model="file" accept=".xlsx, .xls" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                @if($file)
                    <div class="text-cyan-400 font-medium text-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Đã chọn: <span class="font-bold text-white">{{ $file->getClientOriginalName() }}</span>
                    </div>
                @else
                    <p class="text-slate-400 text-sm">Kéo thả file vào đây hoặc <span class="text-cyan-400 font-semibold underline">bấm để chọn file</span></p>
                @endif
            </div>

            @error('file')
                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-400 text-sm font-medium">
                    {{ $message }}
                </div>
            @enderror

            <div class="flex items-center justify-center gap-4">
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-cyan-500/20 disabled:opacity-50">
                    <span wire:loading.remove>Tiếp tục: Xem trước & Kiểm tra dữ liệu</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Đang đọc & Validate dữ liệu...
                    </span>
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- BƯỚC 2: PREVIEW & KHỞI TẠO IMPORT -->
    @if($step === 'preview')
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4">
                <p class="text-xs text-slate-400 uppercase font-semibold">Tổng số dòng</p>
                <p class="text-2xl font-extrabold text-white mt-1">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4">
                <p class="text-xs text-emerald-400 uppercase font-semibold">Hợp lệ (Sẵn sàng import)</p>
                <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $summary['valid'] }}</p>
            </div>
            <div class="bg-rose-500/10 border border-rose-500/20 rounded-2xl p-4">
                <p class="text-xs text-rose-400 uppercase font-semibold">Có lỗi (Bắt buộc sửa hoặc bỏ qua)</p>
                <p class="text-2xl font-extrabold text-rose-400 mt-1">{{ $summary['error'] }}</p>
            </div>
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4">
                <p class="text-xs text-amber-400 uppercase font-semibold">Đã bỏ qua thủ công</p>
                <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $summary['skipped'] }}</p>
            </div>
        </div>

        @if(session()->has('error'))
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400 font-medium text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-4">
            <p class="text-sm text-slate-300">
                Chỉ những dòng <span class="text-emerald-400 font-bold">Hợp lệ</span> và <span class="text-white font-bold">chưa bị bỏ qua</span> mới được nhập vào CSDL.
            </p>
            <div class="flex items-center gap-3">
                <button wire:click="resetUpload" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium transition">
                    Hủy & Chọn file khác
                </button>
                <button wire:click="confirmImport" @if($summary['valid'] === 0) disabled @endif class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-cyan-500/20 disabled:opacity-40 disabled:cursor-not-allowed">
                    Xác nhận Import ({{ $summary['valid'] }} dòng hợp lệ)
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                            <th class="p-3 text-center">Dòng</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3">Mã thiết bị</th>
                            <th class="p-3">Tên thiết bị</th>
                            <th class="p-3">Mã Model</th>
                            <th class="p-3">Mã Khoa</th>
                            <th class="p-3">Mã Vị trí</th>
                            <th class="p-3">Số Serial</th>
                            <th class="p-3">Ngày nhập</th>
                            <th class="p-3">Chi tiết / Chi tiết lỗi</th>
                            <th class="p-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($paginatedRows as $index => $row)
                            <tr class="transition {{ $row['skip'] ? 'opacity-40 bg-slate-950/40' : ($row['status'] === 'valid' ? 'hover:bg-slate-800/40' : 'bg-rose-500/5 hover:bg-rose-500/10') }}">
                                <td class="p-3 text-center font-semibold text-slate-400">{{ $row['row'] }}</td>
                                <td class="p-3">
                                    @if($row['skip'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">Đã bỏ qua</span>
                                    @elseif($row['status'] === 'valid')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Hợp lệ</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Có lỗi</span>
                                    @endif
                                </td>
                                <td class="p-3 font-mono font-semibold text-cyan-400">{{ $row['display']['code'] ?: '-' }}</td>
                                <td class="p-3 font-medium text-white max-w-xs truncate">{{ $row['display']['name'] ?: '-' }}</td>
                                <td class="p-3 font-mono text-slate-300">{{ $row['display']['model_code'] ?: '-' }}</td>
                                <td class="p-3 font-mono text-slate-300">{{ $row['display']['dept_code'] ?: '-' }}</td>
                                <td class="p-3 font-mono text-slate-300">{{ $row['display']['location_code'] ?: '-' }}</td>
                                <td class="p-3 font-mono text-slate-300">{{ $row['display']['serial_number'] ?: '-' }}</td>
                                <td class="p-3 text-slate-400">{{ $row['display']['import_date'] ? \Carbon\Carbon::parse($row['display']['import_date'])->format('d/m/Y') : '-' }}</td>

                                <td class="p-3 text-xs">
                                    @if(!empty($row['errors']))
                                        <ul class="list-disc list-inside text-rose-400 space-y-0.5">
                                            @foreach($row['errors'] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-slate-500">Không có lỗi</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="toggleSkipRow({{ $index }})" class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $row['skip'] ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                                        {{ $row['skip'] ? 'Bỏ qua -> Chọn lại' : 'Bỏ qua dòng này' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($totalPages > 1)
                <div class="p-4 bg-slate-950/60 border-t border-slate-800 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Hiển thị trang <span class="font-bold text-white">{{ $page }}</span> / {{ $totalPages }} (Tổng {{ $totalPreview }} dòng)</p>
                    <div class="flex items-center gap-2">
                        <button wire:click="$set('page', {{ max(1, $page - 1) }})" @if($page <= 1) disabled @endif class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold disabled:opacity-30">
                            Trang trước
                        </button>
                        <button wire:click="$set('page', {{ min($totalPages, $page + 1) }})" @if($page >= $totalPages) disabled @endif class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold disabled:opacity-30">
                            Trang sau
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- BƯỚC 3: HOÀN TẤT -->
    @if($step === 'done')
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-xl mx-auto space-y-6 text-center shadow-2xl">
        <div class="w-20 h-20 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <div>
            <h3 class="text-xl font-bold text-white">Đã nhập dữ liệu thành công!</h3>
            <p class="text-sm text-slate-400 mt-1">Dữ liệu đã được ghi nhận vào cơ sở dữ liệu và lưu lịch sử Import.</p>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-semibold text-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="flex items-center justify-center gap-4">
            <button wire:click="resetUpload" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition">
                Nhập tiếp file khác
            </button>
            <a href="/equipment" class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition">
                Về Danh sách Thiết bị
            </a>
            <a href="/import-batches" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 text-sm font-semibold border border-cyan-500/20 transition">
                Xem Lịch sử Import
            </a>
        </div>
    </div>
    @endif
</div>
