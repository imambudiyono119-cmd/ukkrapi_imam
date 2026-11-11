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
    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 sm:py-4">
        <header class="bg-white rounded-2xl shadow-sm px-4 py-3 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-smkn-1-bantul.png') }}" alt="Logo SMKN 1 Bantul" class="h-10 w-10 sm:h-12 sm:w-12 object-contain">
                    <div>
                        <h1 class="font-bold text-base sm:text-lg leading-tight">RAPI-Sekolah</h1>
                        <p class="text-xs text-slate-500">Agenda & fasilitas sekolah</p>
                    </div>
                </div>

                <!-- Desktop User Actions -->
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        <a href="{{ route('profile') }}" class="text-sm font-medium text-slate-700 hover:text-sky-600 transition">{{ auth()->user()->fullname }}</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg text-sm transition">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-3 py-2 rounded-lg text-sm transition">Login</a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <button
                    id="navbar-toggle"
                    type="button"
                    class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 transition"
                    aria-label="Buka menu navigasi"
                    aria-expanded="false"
                >
                    <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Nav Menu (Toggled by Hamburger) -->
            <nav id="mobile-nav" class="hidden lg:hidden border-t border-slate-100 pt-3 mt-3 space-y-1 text-sm">
                @auth
                    <div class="p-3 mb-2.5 rounded-xl bg-gradient-to-r from-sky-50 to-slate-50 border border-sky-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr(auth()->user()->fullname, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-slate-500 leading-none">Login sebagai</p>
                                <p class="font-semibold text-slate-800 text-sm truncate mt-0.5">{{ auth()->user()->fullname }}</p>
                            </div>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 font-medium shrink-0">
                                {{ auth()->user()->role?->role_name ?? 'Pengguna' }}
                            </span>
                        </div>
                    </div>
                @endauth

                @if (auth()->user()?->isGuru())
                    <a href="{{ route('calendar.public') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('calendar.public', 'home') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('calendar.public', 'home') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Kalender</span>
                    </a>
                    <a href="{{ route('complaints.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('complaints.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('complaints.*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Pengaduan</span>
                    </a>
                @else
                    @unless (request()->routeIs('home', 'calendar.public'))
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('dashboard') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    @endunless
                    <a href="{{ route('calendar.public') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('calendar.public', 'home') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('calendar.public', 'home') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Kalender</span>
                    </a>
                    @if (! auth()->user()?->isStudent())
                        <a href="{{ route('complaints.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('complaints.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('complaints.*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Pengaduan</span>
                        </a>
                    @endif
                    @if (auth()->user()?->isAdmin())
                        <a href="{{ route('master-data.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('master-data.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('master-data.*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Master Data</span>
                        </a>
                        <a href="{{ route('reports.daily') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('reports.daily') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('reports.daily') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Laporan Harian</span>
                        </a>
                        <a href="{{ route('reports.monthly') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('reports.monthly') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('reports.monthly') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Laporan Bulanan</span>
                        </a>
                    @endif
                @endif

                @auth
                    <div class="border-t border-slate-100 pt-2 mt-2 space-y-1">
                        <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition font-medium {{ request()->routeIs('profile*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('profile*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Edit Profil</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-rose-600 hover:bg-rose-50 font-medium transition">
                                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-slate-100 pt-2.5 mt-2">
                        <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 rounded-xl font-medium transition shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Login ke Akun</span>
                        </a>
                    </div>
                @endauth
            </nav>
        </header>

        <main class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <aside class="hidden lg:block lg:col-span-3 bg-white rounded-2xl shadow-sm p-4 h-fit">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('navbar-toggle');
            const mobileNav = document.getElementById('mobile-nav');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');

            if (toggleBtn && mobileNav) {
                toggleBtn.addEventListener('click', function () {
                    const isHidden = mobileNav.classList.toggle('hidden');
                    toggleBtn.setAttribute('aria-expanded', !isHidden);
                    if (hamburgerIcon && closeIcon) {
                        hamburgerIcon.classList.toggle('hidden', !isHidden);
                        closeIcon.classList.toggle('hidden', isHidden);
                    }
                });
            }
        });
    </script>
</body>
</html>
