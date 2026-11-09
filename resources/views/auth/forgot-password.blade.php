@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
        <h1 class="mb-2 text-2xl font-bold">Lupa password?</h1>
        <p class="mb-6 text-sm text-slate-500">Masukkan email pemulihan akun Anda.</p>

        @if (session('status'))
            <p class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <button type="submit" class="w-full rounded-xl bg-sky-600 py-3 font-semibold text-white">Kirim tautan reset</button>
        </form>

        <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-sky-700 hover:underline">Kembali ke login</a>
    </div>
@endsection