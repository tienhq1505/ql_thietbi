<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MEMS - Quản lý Trang thiết bị Y tế' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="h-full text-slate-800 bg-[#f1f5f9] antialiased flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-[#1e3a8a] border-r border-blue-800 flex-shrink-0 flex flex-col justify-between">
        <div>
            <!-- Brand Logo -->
            <div class="p-6 border-b border-blue-700/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white text-[#1e3a8a] flex items-center justify-center font-black text-xl shadow-md">
                    ✚
                </div>
                <div>
                    <h1 class="font-bold text-base text-white tracking-wide leading-tight">MEMS Hospital</h1>
                    <p class="text-xs text-blue-200">Quản lý Trang thiết bị</p>
                </div>
            </div>

            <!-- User Info Badge -->
            @auth
            <div class="p-4 mx-3 my-4 bg-white/10 rounded-2xl border border-white/15">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/20 text-white font-bold flex items-center justify-center text-sm border border-white/30">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-blue-200 font-medium truncate">
                            {{ auth()->user()->roles->first()?->display_name ?? auth()->user()->roles->first()?->name ?? 'Người dùng' }}
                        </p>
                        @if(auth()->user()->department)
                            <p class="text-[10px] text-blue-200/80 truncate mt-0.5">📍 {{ auth()->user()->department->name }}</p>
                        @endif
                        <a href="{{ route('profile.password') }}" class="mt-2 inline-flex items-center gap-1 text-[10px] text-blue-200 hover:text-white transition font-semibold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Đổi mật khẩu
                        </a>
                    </div>
                </div>
            </div>
            @endauth

            <!-- Navigation Links -->
            <nav class="px-3 space-y-1">
                <a href="/dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('dashboard*') || request()->is('/') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Trang Chủ / Dashboard</span>
                </a>

                @can('equipment.view')
                <a href="/equipment" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('equipment') || request()->is('equipment/*') && !request()->is('equipment/import*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    <span>Quản lý Thiết bị</span>
                </a>

                <a href="/reports/documents" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('reports*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Báo cáo Hồ sơ</span>
                </a>
                @endcan

                @can('equipment.import')
                <a href="/equipment/import" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('equipment/import*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Nhập Excel Thiết bị</span>
                </a>

                <a href="/import-batches" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('import-batches*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Lịch sử Import</span>
                </a>
                @endcan


                @can('master_data.manage')
                <a href="/categories" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('categories*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Danh mục Hệ thống</span>
                </a>
                @endcan

                @can('audit_log.view')
                <a href="/audit-logs" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('audit-logs*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Audit Log</span>
                </a>
                @endcan

                @can('inventory.manage')
                <a href="/inventory" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('inventory*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Kiểm kê & QR Code</span>
                </a>
                @endcan

                @can('users.manage')
                <a href="/users" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('users*') ? 'bg-white/20 text-white font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Quản lý Người dùng</span>
                </a>
                @endcan
            </nav>
        </div>

        <!-- Footer / Logout -->
        <div class="p-4 border-t border-blue-700/50">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-100 border border-rose-400/30 text-xs font-semibold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 md:p-8 overflow-y-auto">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
