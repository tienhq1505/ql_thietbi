<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Chỉnh Sửa Hồ Sơ Thiết Bị</h1>
            <p class="text-xs text-slate-400 mt-1">Cập nhật thông tin chi tiết cho mã thiết bị: <code class="text-cyan-400 font-mono">{{ $equipment->code }}</code></p>
        </div>
        <a href="{{ route('equipment.show', $equipment->id) }}" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
            Hủy / Quay lại
        </a>
    </div>

    <form wire:submit="save" class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <!-- Group 1: General Info -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-2">1. Thông tin chung & Nhận diện</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Mã thiết bị <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="code" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold focus:border-cyan-500">
                    @error('code') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Số Serial</label>
                    <input type="text" wire:model="serial_number" placeholder="Bỏ trống nếu chưa có..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:border-cyan-500">
                    @error('serial_number') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Loại sở hữu</label>
                    <select wire:model="ownership_type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        @foreach($ownershipTypes as $ot)
                            <option value="{{ $ot->value }}">{{ $ot->label() }}</option>
                        @endforeach
                    </select>
                    @error('ownership_type') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Model sản phẩm <span class="text-rose-400">*</span></label>
                    <select wire:model.live="equipment_model_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn Model --</option>
                        @foreach($models as $mod)
                            <option value="{{ $mod->id }}">{{ $mod->name }} ({{ $mod->manufacturer?->name }})</option>
                        @endforeach
                    </select>
                    @error('equipment_model_id') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-slate-300 font-semibold mb-1">Tên thiết bị <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-cyan-500">
                    @error('name') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Loại thiết bị <span class="text-rose-400">*</span></label>
                    <select wire:model="category_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn loại --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Hãng sản xuất <span class="text-rose-400">*</span></label>
                    <select wire:model="manufacturer_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn hãng --</option>
                        @foreach($manufacturers as $mfg)
                            <option value="{{ $mfg->id }}">{{ $mfg->name }} ({{ $mfg->country }})</option>
                        @endforeach
                    </select>
                    @error('manufacturer_id') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nước sản xuất</label>
                    <input type="text" wire:model="origin_country" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Năm sản xuất</label>
                    <input type="number" wire:model="manufacture_year" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-cyan-500">
                </div>
            </div>
        </div>

        <!-- Group 2: Department & Location -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-2">2. Quản lý & Vị trí phân bổ</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Khoa / Phòng quản lý <span class="text-rose-400">*</span></label>
                    <select wire:model.live="department_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn khoa --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="text-rose-400 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Vị trí lắp đặt chi tiết</label>
                    <select wire:model="location_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn vị trí --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->building }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Người phụ trách (Tài khoản)</label>
                    <select wire:model="manager_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn cán bộ phụ trách --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->department?->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Group 3: Financial & Status -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-2">3. Giá trị, Bảo hành & Trạng thái</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Giá mua (VNĐ)</label>
                    <input type="number" wire:model="purchase_price" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nguồn kinh phí</label>
                    <input type="text" wire:model="funding_source" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nhà cung cấp</label>
                    <select wire:model="supplier_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        <option value="">-- Chọn nhà cung cấp --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Tình trạng kỹ thuật <span class="text-rose-400">*</span></label>
                    <select wire:model="condition" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        @foreach($conditions as $cond)
                            <option value="{{ $cond->value }}">{{ $cond->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Trạng thái sử dụng <span class="text-rose-400">*</span></label>
                    <select wire:model="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:border-cyan-500">
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Hạn bảo hành</label>
                    <input type="date" wire:model="warranty_expiration_date" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-cyan-500">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('equipment.show', $equipment->id) }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                Hủy
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-500/20 transition">
                Cập Nhật Hồ Sơ
            </button>
        </div>
    </form>
</div>
