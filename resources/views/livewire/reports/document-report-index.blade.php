<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Báo cáo Tổng hợp Hồ sơ Thiết bị Y tế</h1>
            <p class="text-sm text-slate-400 mt-1">Thống kê độ phủ hồ sơ giấy tờ theo từng Khoa / Phòng trong toàn viện</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Xuất Báo cáo Excel (.xlsx)</span>
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    @if(!$isKhoaScope)
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center gap-4">
        <div class="w-64">
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Lọc theo Khoa / Phòng</label>
            <select wire:model.live="department_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:border-cyan-500">
                <option value="">-- Tất cả Khoa / Phòng --</option>
                @foreach($allDepartmentsList as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        @if($department_id)
        <button wire:click="$set('department_id', null)" class="mt-5 text-xs text-slate-400 hover:text-rose-400 underline">Xóa lọc</button>
        @endif
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-950/90 text-slate-300 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-3 py-3 w-10 text-center border-r border-slate-800">STT</th>
                        <th class="px-4 py-3 border-r border-slate-800 min-w-[180px]">Khoa / Phòng</th>
                        <th class="px-3 py-3 text-center border-r border-slate-800">Tổng TB</th>
                        @foreach($docTypes as $dt)
                            <th class="px-3 py-3 text-center border-r border-slate-800 min-w-[110px] text-[11px]">
                                {{ $dt->label() }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($reportData as $index => $row)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-3 py-3 text-center font-mono text-slate-400 border-r border-slate-800/60">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-bold text-white border-r border-slate-800/60">{{ $row['department']->name }}</td>
                        <td class="px-3 py-3 text-center font-mono font-bold text-cyan-400 border-r border-slate-800/60">{{ $row['eq_count'] }}</td>
                        @foreach($docTypes as $dt)
                            @php
                                $d = $row['docs'][$dt->value];
                            @endphp
                            <td class="px-3 py-3 text-center border-r border-slate-800/60 font-mono">
                                <span class="font-bold text-slate-200">{{ $d['has'] }}/{{ $d['total'] }}</span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">({{ $d['pct'] }}%)</span>
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($docTypes) + 3 }}" class="px-4 py-12 text-center text-slate-400">
                            Không có dữ liệu báo cáo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Footer Total Row -->
                <tfoot class="bg-slate-950 font-bold text-white border-t-2 border-slate-700">
                    <tr>
                        <td class="px-3 py-3 text-center border-r border-slate-800"></td>
                        <td class="px-4 py-3 uppercase tracking-wider text-cyan-400 border-r border-slate-800">TỔNG CỘNG TOÀN VIỆN</td>
                        <td class="px-3 py-3 text-center font-mono text-cyan-300 border-r border-slate-800 text-sm">{{ $totalEqCount }}</td>
                        @foreach($docTypes as $dt)
                            @php
                                $hasSum = $totalDocCounts[$dt->value];
                                $pctSum = $totalEqCount > 0 ? round(($hasSum / $totalEqCount) * 100, 1) : 0;
                            @endphp
                            <td class="px-3 py-3 text-center border-r border-slate-800 font-mono">
                                <span class="text-cyan-300">{{ $hasSum }}/{{ $totalEqCount }}</span>
                                <span class="block text-[10px] text-slate-400 font-normal">({{ $pctSum }}%)</span>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
