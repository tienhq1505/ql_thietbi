<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Hệ thống Quản lý Trang thiết bị Y tế - MEMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="h-full bg-gradient-to-br from-blue-50 via-sky-50 to-slate-100 text-slate-800 flex items-center justify-center p-4 min-h-screen">
    {{ $slot }}
    @livewireScripts
</body>
</html>
