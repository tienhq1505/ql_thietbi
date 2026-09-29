<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                Lịch sử Import Thiết bị
            </h1>
            <p class="text-sm text-slate-400 mt-1">Theo dõi danh sách các đợt nhập Excel, số lượng thiết bị thành công và chi tiết nhật ký lỗi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/equipment/import" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-sm font-bold transition shadow-lg shadow-cyan-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nhập Excel Mới
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                        <th class="p-4">Mã Đợt (ID)</th>
                        <th class="p-4">Tên File</th>
                        <th class="p-4">Người thực hiện</th>
                        <th class="p-4">Thời gian</th>
                        <th class="p-4 text-center">Tổng dòng</th>
                        <th class="p-4 text-center">Thành công</th>
                        <th class="p-4 text-center">Lỗi / Bỏ qua</th>
                        <th class="p-4">Trạng thái</th>
                        <th class="p-4 text-center">Chi tiết lỗi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($batches as $b)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-4 font-mono font-bold text-cyan-400">#{{ $b->id }}</td>
                            <td class="p-4 font-medium text-white max-w-xs truncate">{{ $b->file_name }}</td>
                            <td class="p-4 text-slate-300">
                                <span class="font-semibold text-white">{{ $b->importedBy?->name ?? 'N/A' }}</span>
                                <span class="text-xs text-slate-500 block">{{ $b->importedBy?->email }}</span>
                            </td>
                            <td class="p-4 text-slate-400 text-xs">{{ $b->created_at->format('H:i d/m/Y') }}</td>
                            <td class="p-4 text-center font-semibold text-slate-300">{{ $b->total_rows }}</td>
                            <td class="p-4 text-center font-semibold text-emerald-400">{{ $b->success_rows }}</td>
                            <td class="p-4 text-center font-semibold text-rose-400">{{ $b->failed_rows }}</td>
                            <td class="p-4">
                                @if($b->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Hoàn tất 100%</span>
                                @elseif($b->status === 'completed_with_errors')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Hoàn tất có lỗi</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Đang xử lý</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if(!empty($b->error_details))
                                    <button wire:click="viewDetails({{ $b->id }})" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-400 border border-rose-500/20 text-xs font-semibold transition">
                                        Xem chi tiết lỗi ({{ count($b->error_details) }})
                                    </button>
                                @else
                                    <span class="text-slate-500 text-xs">Không có lỗi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500">
                                Chưa có lịch sử import nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-950/60 border-t border-slate-800">
            {{ $batches->links() }}
        </div>
    </div>

    <!-- Modal Xem Chi tiết Lỗi -->
    @if($showModal && $selectedBatch)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-white">Chi tiết Nhật ký Lỗi — Đợt Import #{{ $selectedBatch->id }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">File: {{ $selectedBatch->file_name }} | Người nhập: {{ $selectedBatch->importedBy?->name }}</p>
                </div>
                <button wire:click="closeModal" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                @if(!empty($selectedBatch->error_details))
                    <div class="space-y-3">
                        @foreach($selectedBatch->error_details as $errRow)
                            <div class="p-4 bg-rose-500/5 border border-rose-500/20 rounded-xl space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-rose-400">Dòng Excel {{ $errRow['row'] ?? '?' }} (Mã thiết bị: {{ $errRow['code'] ?? 'N/A' }})</span>
                                </div>
                                <ul class="list-disc list-inside text-xs text-slate-300 space-y-0.5 mt-1">
                                    @foreach($errRow['errors'] ?? [] as $er)
                                        <li>{{ $er }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-center text-sm">Không tìm thấy chi tiết lỗi.</p>
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
