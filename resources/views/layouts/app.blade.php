<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-7xl mx-auto px-4 py-4">
        <header class="flex items-center justify-between bg-white rounded-2xl shadow-sm px-4 py-3 mb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-smkn-1-bantul.png') }}" alt="Logo SMKN 1 Bantul" class="h-12 w-12 object-contain">
                <div>
                    <h1 class="font-bold text-lg">RAPI-Sekolah</h1>
                    <p class="text-xs text-slate-500">Agenda & fasilitas sekolah</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('profile') }}" class="text-sm text-slate-700">{{ auth()->user()->fullname }}</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="bg-slate-800 text-white px-3 py-2 rounded-lg text-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-sky-600 text-white px-3 py-2 rounded-lg text-sm">Login</a>
                @endauth
            </div>
        </header>

        <main class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <aside class="lg:col-span-3 bg-white rounded-2xl shadow-sm p-4">
                <nav class="space-y-2 text-sm">
                    @if (auth()->user()?->isGuru())
                        <a href="{{ route('calendar.public') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Kalender</a>
                        <a href="{{ route('complaints.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Pengaduan</a>
                    @else
                        @unless (request()->routeIs('home', 'calendar.public'))
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Dashboard</a>
                        @endunless
                        <a href="{{ route('calendar.public') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Kalender</a>
                        @if (! auth()->user()?->isStudent())
                            <a href="{{ route('complaints.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Pengaduan</a>
                        @endif
                        @if (auth()->user()?->isAdmin())
                            <a href="{{ route('master-data.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Master Data</a>
                            <a href="{{ route('reports.daily') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Laporan Harian</a>
                            <a href="{{ route('reports.monthly') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Laporan Bulanan</a>
                        @endif
                    @endif
                </nav>
            </aside>
            <section class="lg:col-span-9">
                @php $notifications = $notifications ?? []; @endphp
                @if (!empty($notifications))
                    <div class="mb-4 space-y-2">
                        @foreach ($notifications as $notification)
                            <div class="rounded-xl border border-sky-200 bg-sky-50 text-sky-800 px-3 py-2 text-sm">
                                {{ $notification['message'] ?? '' }}
                            </div>
                        @endforeach
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800 px-3 py-2 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-100 border border-red-300 text-red-800 px-3 py-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
