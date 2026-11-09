<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg" aria-label="RAPI-Sekolah, beranda">
                <img src="{{ asset('images/logo-smkn-1-bantul.png') }}" alt="Logo SMKN 1 Bantul" class="h-10 w-10 object-contain">
                <span>RAPI-Sekolah</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="border border-slate-200 text-slate-700 px-3 py-2 rounded-xl text-sm font-medium">Beranda</a>
                <a href="{{ route('login') }}" class="bg-sky-600 text-white px-4 py-2 rounded-xl text-sm font-medium">Login</a>
            </div>
        </div>
    </header>
    <div class="min-h-screen flex items-center justify-center p-4">
        @yield('content')
    </div>
</body>
</html>
