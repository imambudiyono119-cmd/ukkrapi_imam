<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-3.5 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between gap-2 sm:gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-base sm:text-lg text-slate-800" aria-label="RAPI-Sekolah, beranda">
                <img src="{{ asset('images/logo-smkn-1-bantul.png') }}" alt="Logo SMKN 1 Bantul" class="h-9 w-9 sm:h-10 sm:w-10 object-contain">
                <span class="truncate">RAPI-Sekolah</span>
            </a>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('home') }}" class="border border-slate-200 text-slate-700 px-3 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 transition">Beranda</a>
                <a href="{{ route('login') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-medium transition shadow-xs">Login</a>
            </div>
        </div>
    </header>
    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center p-3 sm:p-4">
        @yield('content')
    </div>
</body>
</html>
