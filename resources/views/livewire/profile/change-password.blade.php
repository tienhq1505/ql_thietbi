<div class="max-w-xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
            <div class="p-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            Đổi Mật Khẩu Cá Nhân
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Thay đổi mật khẩu đăng nhập cho tài khoản <strong class="text-slate-800">{{ auth()->user()->email }}</strong>.
        </p>
    </div>

    <!-- Flash message -->
    @if(session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Card Form -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <form wire:submit="updatePassword" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Mật khẩu hiện tại <span class="text-rose-500">*</span>
                </label>
                <input type="password" wire:model="current_password" placeholder="Nhập mật khẩu đang sử dụng..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                @error('current_password') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Mật khẩu mới <span class="text-rose-500">*</span> (tối thiểu 8 ký tự)
                </label>
                <input type="password" wire:model="password" placeholder="Nhập mật khẩu mới..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                @error('password') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Xác nhận mật khẩu mới <span class="text-rose-500">*</span>
                </label>
                <input type="password" wire:model="password_confirmation" placeholder="Nhập lại mật khẩu mới..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                @error('password_confirmation') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <a href="/dashboard" class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition">
                    &larr; Quay lại trang chủ
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu Mật Khẩu Mới
                </button>
            </div>
        </form>
    </div>
</div>
