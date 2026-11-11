@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md rounded-2xl bg-white p-5 sm:p-6 shadow-lg">
        <h1 class="mb-2 text-2xl font-bold">Atur Ulang Password</h1>
        <p class="mb-6 text-sm text-slate-500">Masukkan kode OTP yang dikirimkan ke email dan buat password baru untuk akun Anda.</p>

        @if (session('status'))
            <p class="mb-4 rounded-lg bg-sky-50 px-3 py-2 text-sm text-sky-800">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            @if (!empty($token) && strlen($token) > 10)
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-800">
                    ✓ Token verifikasi email terdeteksi otomatis.
                </div>
            @else
                <div>
                    <label for="token" class="mb-1 block text-sm font-medium">Kode OTP (6 Digit)</label>
                    <input id="token" type="text" name="token" value="{{ old('token', $token) }}" maxlength="64" placeholder="Contoh: 123456" required class="w-full rounded-xl border px-3 py-3 font-mono text-base tracking-wider focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="mt-1 text-xs text-slate-500">Periksa kotak masuk atau spam email Anda untuk melihat 6 digit kode OTP.</p>
                </div>
            @endif

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email Akun</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password Baru</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required placeholder="Minimal 8 karakter" class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Ulangi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Ulangi password baru" class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <button type="submit" class="w-full rounded-xl bg-sky-600 py-3 font-semibold text-white transition hover:bg-sky-700">Simpan Password Baru</button>
        </form>

        <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-sky-700 hover:underline">Kembali ke login</a>
    </div>
@endsection