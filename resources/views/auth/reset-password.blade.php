@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
        <h1 class="mb-2 text-2xl font-bold">Atur ulang password</h1>
        <p class="mb-6 text-sm text-slate-500">Buat password baru untuk akun Anda.</p>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password baru</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Ulangi password baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <button type="submit" class="w-full rounded-xl bg-sky-600 py-3 font-semibold text-white">Simpan password baru</button>
        </form>
    </div>
@endsection