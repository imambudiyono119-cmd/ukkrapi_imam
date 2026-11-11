@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-5 sm:p-6">
        <h1 class="text-2xl font-bold mb-2">Login RAPI-Sekolah</h1>
        <p class="text-sm text-slate-500 mb-6">Masuk ke dashboard sesuai role Anda.</p>

        @if (session('status'))
            <p class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        @error('username')
            <p role="alert" class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input name="username" value="{{ old('username') }}" autocomplete="username" class="w-full border rounded-xl px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500" required>
            </div>
            <div>
                <label for="login-password" class="mb-1 block text-sm font-medium">Password</label>
                <div class="flex rounded-xl border focus-within:ring-2 focus-within:ring-sky-500">
                    <input id="login-password" type="password" name="password" autocomplete="current-password" class="min-w-0 flex-1 rounded-l-xl px-3 py-3 focus:outline-none" required>
                    <button
                        type="button"
                        id="toggle-password"
                        aria-label="Lihat password"
                        aria-pressed="false"
                        class="rounded-r-xl px-3 text-slate-500 hover:bg-slate-100"
                    >
                        <svg data-eye-icon aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg data-eye-off-icon aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 5.35A10.7 10.7 0 0 1 12 5.25c6.25 0 9.75 6.75 9.75 6.75a16.2 16.2 0 0 1-3.2 4.1M6.2 6.2C3.6 8 2.25 12 2.25 12S5.75 18.75 12 18.75c1.4 0 2.65-.35 3.75-.9" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-sky-600 text-white rounded-xl py-3 font-semibold">Masuk</button>
        </form>
        <a href="{{ route('password.request') }}" class="mt-4 block text-center text-sm font-medium text-sky-700 hover:underline">Lupa password?</a>
    </div>

    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const passwordInput = document.getElementById('login-password');
            const isPasswordHidden = passwordInput.type === 'password';

            passwordInput.type = isPasswordHidden ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(isPasswordHidden));
            this.setAttribute('aria-label', isPasswordHidden ? 'Sembunyikan password' : 'Lihat password');
            this.querySelector('[data-eye-icon]').classList.toggle('hidden', isPasswordHidden);
            this.querySelector('[data-eye-off-icon]').classList.toggle('hidden', !isPasswordHidden);
        });
    </script>
@endsection
