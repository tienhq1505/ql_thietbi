<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Quản lý Danh mục Hệ thống (Master Data)</h1>
            <p class="text-xs text-slate-400 mt-1">Danh mục Loại thiết bị, Hãng sản xuất, Model, Nhà cung cấp, Vị trí lắp đặt, và Khoa/Phòng</p>
        </div>
        <div>
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-cyan-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Thêm Mới {{ match($activeTab) {
                    'categories' => 'Loại Thiết Bị',
                    'manufacturers' => 'Hãng Sản Xuất',
                    'models' => 'Model Sản Phẩm',
                    'suppliers' => 'Nhà Cung Cấp',
                    'locations' => 'Vị Trí Lắp Đặt',
                    'departments' => 'Khoa / Phòng',
                    default => 'Bản ghi'
                } }}</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabs Header -->
    <div class="flex items-center gap-2 border-b border-slate-800 text-xs font-semibold overflow-x-auto">
        <button wire:click="$set('activeTab', 'categories')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'categories' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Loại Thiết Bị ({{ $categories->count() }})
        </button>
        <button wire:click="$set('activeTab', 'manufacturers')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'manufacturers' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Hãng Sản Xuất ({{ $manufacturers->count() }})
        </button>
        <button wire:click="$set('activeTab', 'models')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'models' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Model Sản Phẩm ({{ $models->count() }})
        </button>
        <button wire:click="$set('activeTab', 'suppliers')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'suppliers' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Nhà Cung Cấp ({{ $suppliers->count() }})
        </button>
        <button wire:click="$set('activeTab', 'locations')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'locations' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Vị Trí Lắp Đặt ({{ $locations->count() }})
        </button>
        <button wire:click="$set('activeTab', 'departments')" class="px-4 py-3 border-b-2 transition whitespace-nowrap {{ $activeTab === 'departments' ? 'border-cyan-400 text-cyan-400 font-bold' : 'border-transparent text-slate-400 hover:text-white' }}">
            Khoa / Phòng ({{ $departments->count() }})
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        @if($activeTab === 'categories')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã loại</th>
                    <th class="px-4 py-3">Tên loại thiết bị</th>
                    <th class="px-4 py-3">Mô tả</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($categories as $cat)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $cat->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $cat->description }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $cat->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $cat->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $cat->id }})" wire:confirm="Bạn có chắc muốn xóa loại thiết bị này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @elseif($activeTab === 'manufacturers')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã hãng</th>
                    <th class="px-4 py-3">Tên Hãng sản xuất</th>
                    <th class="px-4 py-3">Quốc gia</th>
                    <th class="px-4 py-3">Website</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($manufacturers as $mfg)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $mfg->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $mfg->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $mfg->country }}</td>
                    <td class="px-4 py-3 text-cyan-400 underline">{{ $mfg->website }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $mfg->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $mfg->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $mfg->id }})" wire:confirm="Bạn có chắc muốn xóa hãng sản xuất này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @elseif($activeTab === 'models')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã Model</th>
                    <th class="px-4 py-3">Tên Model sản phẩm</th>
                    <th class="px-4 py-3">Loại thiết bị</th>
                    <th class="px-4 py-3">Hãng SX</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($models as $mod)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $mod->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $mod->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $mod->category?->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $mod->manufacturer?->name }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $mod->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $mod->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $mod->id }})" wire:confirm="Bạn có chắc muốn xóa Model này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @elseif($activeTab === 'suppliers')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã nhà CC</th>
                    <th class="px-4 py-3">Tên Nhà cung cấp</th>
                    <th class="px-4 py-3">Số điện thoại</th>
                    <th class="px-4 py-3">Địa chỉ</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($suppliers as $sup)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $sup->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $sup->name }}</td>
                    <td class="px-4 py-3 font-mono text-slate-300">{{ $sup->phone }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $sup->address }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $sup->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $sup->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $sup->id }})" wire:confirm="Bạn có chắc muốn xóa nhà cung cấp này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @elseif($activeTab === 'locations')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã vị trí</th>
                    <th class="px-4 py-3">Tên phòng / vị trí</th>
                    <th class="px-4 py-3">Khoa / Phòng</th>
                    <th class="px-4 py-3">Tòa nhà / Tầng / Phòng</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($locations as $loc)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $loc->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $loc->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $loc->department?->name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $loc->building }} | {{ $loc->floor }} | {{ $loc->room }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $loc->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $loc->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $loc->id }})" wire:confirm="Bạn có chắc muốn xóa vị trí này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @elseif($activeTab === 'departments')
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Mã Khoa</th>
                    <th class="px-4 py-3">Tên Khoa / Phòng</th>
                    <th class="px-4 py-3">Tên viết tắt</th>
                    <th class="px-4 py-3">SĐT & Trưởng khoa</th>
                    <th class="px-4 py-3 text-right">Số lượng TB</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($departments as $dept)
                <tr class="hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-mono font-semibold text-cyan-400">{{ $dept->code }}</td>
                    <td class="px-4 py-3 font-bold text-white">{{ $dept->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $dept->short_name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $dept->phone }} {{ $dept->head_name ? '('.$dept->head_name.')' : '' }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-300">{{ $dept->equipment_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $dept->id }})" class="px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 rounded-lg border border-cyan-500/20 font-medium">Sửa</button>
                        <button wire:click="delete({{ $dept->id }})" wire:confirm="Bạn có chắc muốn xóa khoa/phòng này?" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/20 font-medium">Xóa</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white">
                    {{ $editingId ? 'Chỉnh sửa' : 'Thêm mới' }} 
                    {{ match($activeTab) {
                        'categories' => 'Loại Thiết Bị',
                        'manufacturers' => 'Hãng Sản Xuất',
                        'models' => 'Model Sản Phẩm',
                        'suppliers' => 'Nhà Cung Cấp',
                        'locations' => 'Vị Trí Lắp Đặt',
                        'departments' => 'Khoa / Phòng',
                        default => 'Bản ghi'
                    } }}
                </h3>
                <button wire:click="resetForm" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form wire:submit="save" class="space-y-4 text-xs">
                <!-- Common Field: Code -->
                <div>
                    <label class="block text-slate-400 mb-1">Mã <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="code" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    @error('code') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Common Field: Name -->
                <div>
                    <label class="block text-slate-400 mb-1">Tên <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    @error('name') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                @if($activeTab === 'categories')
                <div>
                    <label class="block text-slate-400 mb-1">Mô tả</label>
                    <textarea wire:model="description" rows="3" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                @elseif($activeTab === 'manufacturers')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Quốc gia</label>
                        <input type="text" wire:model="country" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Website</label>
                        <input type="text" wire:model="website" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                </div>

                @elseif($activeTab === 'models')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Loại thiết bị <span class="text-rose-400">*</span></label>
                        <select wire:model="category_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="">-- Chọn loại --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Hãng sản xuất <span class="text-rose-400">*</span></label>
                        <select wire:model="manufacturer_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="">-- Chọn hãng --</option>
                            @foreach($manufacturers as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('manufacturer_id') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Thông số kỹ thuật</label>
                    <textarea wire:model="specs" rows="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                @elseif($activeTab === 'suppliers')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Số điện thoại</label>
                        <input type="text" wire:model="phone" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Địa chỉ</label>
                    <input type="text" wire:model="address" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                @elseif($activeTab === 'locations')
                <div>
                    <label class="block text-slate-400 mb-1">Khoa / Phòng quản lý <span class="text-rose-400">*</span></label>
                    <select wire:model="department_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="">-- Chọn khoa --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="text-rose-400 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Tòa nhà</label>
                        <input type="text" wire:model="building" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Tầng</label>
                        <input type="text" wire:model="floor" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Phòng</label>
                        <input type="text" wire:model="room" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                </div>

                @elseif($activeTab === 'departments')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Tên viết tắt</label>
                        <input type="text" wire:model="short_name" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Số điện thoại liên hệ</label>
                        <input type="text" wire:model="phone" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Tên Trưởng khoa / Phòng</label>
                    <input type="text" wire:model="head_name" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>
                @endif

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="resetForm" class="px-4 py-2 bg-slate-800 text-slate-300 hover:bg-slate-700 rounded-xl font-semibold">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl">Lưu dữ liệu</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
