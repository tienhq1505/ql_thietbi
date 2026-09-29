<div class="w-full max-w-md">
    <!-- Header Logo & Title -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 mb-4 shadow-lg shadow-cyan-500/10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-white">MEMS Hospital</h1>
        <p class="text-sm text-slate-400 mt-1">Hệ thống Quản lý Danh mục & Kiểm kê Trang thiết bị Y tế</p>
    </div>

    <!-- Login Card -->
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-8 shadow-2xl shadow-cyan-950/50">
        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Email Đăng Nhập</label>
                <input type="email" id="email" wire:model="email" placeholder="email@mems.local" class="w-full px-4 py-3 bg-slate-900/80 border border-slate-700 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 rounded-xl text-white placeholder-slate-500 text-sm transition-all duration-200">
                @error('email') <span class="text-xs text-rose-400 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Mật Khẩu</label>
                <input type="password" id="password" wire:model="password" placeholder="••••••••" class="w-full px-4 py-3 bg-slate-900/80 border border-slate-700 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 rounded-xl text-white placeholder-slate-500 text-sm transition-all duration-200">
                @error('password') <span class="text-xs text-rose-400 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500 focus:ring-cyan-500/20">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <span class="text-xs text-slate-500">Giai đoạn Demo (Session: 120 phút)</span>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-cyan-500/25 transition-all duration-200 flex items-center justify-center gap-2 group">
                <span>Đăng Nhập Hệ Thống</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <!-- Demo Accounts Section -->
        @if(app()->environment('local', 'testing'))
        <div class="mt-8 pt-6 border-t border-slate-700/50">
            <p class="text-xs font-semibold text-slate-400 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Chọn tài khoản Demo (Mật khẩu: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-cyan-300 font-mono">password</code>)</span>
            </p>
            <div class="grid grid-cols-1 gap-2 text-xs">
                <button type="button" wire:click="fillDemoAccount('admin@mems.local')" class="w-full p-2.5 bg-slate-900/60 hover:bg-slate-700/50 border border-slate-700/50 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-medium text-slate-200">Quản trị hệ thống</span>
                    <span class="text-cyan-400 group-hover:underline">admin@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('vattu@mems.local')" class="w-full p-2.5 bg-slate-900/60 hover:bg-slate-700/50 border border-slate-700/50 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-medium text-slate-200">Phòng Vật tư-TBYT</span>
                    <span class="text-cyan-400 group-hover:underline">vattu@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('kythuat@mems.local')" class="w-full p-2.5 bg-slate-900/60 hover:bg-slate-700/50 border border-slate-700/50 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-medium text-slate-200">Kỹ thuật viên</span>
                    <span class="text-cyan-400 group-hover:underline">kythuat@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('khoa@mems.local')" class="w-full p-2.5 bg-slate-900/60 hover:bg-slate-700/50 border border-slate-700/50 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-medium text-slate-200">Khoa sử dụng (Khoa Nội)</span>
                    <span class="text-cyan-400 group-hover:underline">khoa@mems.local</span>
                </button>
                <button type="button" wire:click="fillDemoAccount('giamdoc@mems.local')" class="w-full p-2.5 bg-slate-900/60 hover:bg-slate-700/50 border border-slate-700/50 rounded-xl text-left flex items-center justify-between group transition">
                    <span class="font-medium text-slate-200">Ban Giám đốc</span>
                    <span class="text-cyan-400 group-hover:underline">giamdoc@mems.local</span>
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
