<div class="w-full max-w-md my-8">
    <!-- Header Logo & Title -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white mb-3 shadow-lg shadow-blue-500/25">
            <span class="text-3xl font-black">✚</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">MEMS Hospital</h1>
        <p class="text-sm text-slate-500 mt-1">Hệ thống Quản lý Danh mục & Kiểm kê Trang thiết bị Y tế</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-8 shadow-xl shadow-blue-900/5">
        <form wire:submit="login" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Email Đăng Nhập</label>
                <input type="email" id="email" wire:model="email" placeholder="email@mems.local" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-slate-900 placeholder-slate-400 text-sm transition duration-150">
                @error('email') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Mật Khẩu</label>
                <input type="password" id="password" wire:model="password" placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-slate-900 placeholder-slate-400 text-sm transition duration-150">
                @error('password') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between text-sm pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 text-xs font-medium">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <span class="text-[11px] text-slate-400">Phiên: {{ config('session.lifetime', 120) }} phút</span>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md shadow-blue-600/20 transition duration-150 flex items-center justify-center gap-2 group">
                <span>Đăng Nhập Hệ Thống</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <!-- Demo Accounts Section -->
        @if(app()->environment('local', 'testing'))
        <div class="mt-6 pt-5 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-600 mb-2.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Chọn tài khoản Demo (Mật khẩu: <code class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-mono font-semibold">password</code>)</span>
            </p>
            <div class="grid grid-cols-1 gap-1.5 text-xs">
                <button type="button" wire:click="fillDemoAccount('admin@mems.local')" class="w-full p-2 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-semibold text-slate-700">Quản trị hệ thống</span>
                    <span class="text-blue-600 font-medium">admin@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('vattu@mems.local')" class="w-full p-2 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-semibold text-slate-700">Phòng Vật tư-TBYT</span>
                    <span class="text-blue-600 font-medium">vattu@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('kythuat@mems.local')" class="w-full p-2 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-semibold text-slate-700">Kỹ thuật viên</span>
                    <span class="text-blue-600 font-medium">kythuat@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('khoa@mems.local')" class="w-full p-2 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-semibold text-slate-700">Khoa sử dụng (Khoa Nội)</span>
                    <span class="text-blue-600 font-medium">khoa@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('giamdoc@mems.local')" class="w-full p-2 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-semibold text-slate-700">Ban Giám đốc</span>
                    <span class="text-blue-600 font-medium">giamdoc@mems.local</span>
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
