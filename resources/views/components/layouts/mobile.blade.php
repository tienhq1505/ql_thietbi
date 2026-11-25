<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MEMS Hospital - Quét QR' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="h-full text-slate-800 bg-[#f1f5f9] antialiased min-h-screen flex flex-col">
    <!-- Compact Top Bar (Mobile-first, No Sidebar) -->
    <header x-data="{ mobileMenuOpen: false }" class="bg-[#1e3a8a] text-white sticky top-0 z-40 shadow-md">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Brand / System Name -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-white text-[#1e3a8a] flex items-center justify-center font-black text-base shadow-sm group-hover:scale-105 transition-transform">
                    ✚
                </div>
                <div>
                    <span class="font-bold text-sm tracking-wide block leading-tight">MEMS Hospital</span>
                    <span class="text-[10px] text-blue-200 block">Quản lý Trang thiết bị</span>
                </div>
            </a>

            <!-- Menu dropdown toggle button -->
            <div class="relative">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-semibold transition" aria-label="Menu">
                    @auth
                        <span class="max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                    @endauth
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': mobileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div x-show="mobileMenuOpen" 
                     @click.outside="mobileMenuOpen = false" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-100" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-75" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95" 
                     class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 text-slate-700 z-50">
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold hover:bg-slate-50 hover:text-blue-600 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Bàn làm việc (Dashboard)</span>
                    </a>

                    <a href="{{ route('equipment.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold hover:bg-slate-50 hover:text-blue-600 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Danh sách thiết bị</span>
                    </a>

                    <a href="{{ route('profile.password') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold hover:bg-slate-50 hover:text-blue-600 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Đổi mật khẩu</span>
                    </a>

                    <div class="my-1 border-t border-slate-100"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container: Thẻ thiết bị nằm ngay đầu trang, không có aside -->
    <main class="flex-1 p-3 sm:p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
