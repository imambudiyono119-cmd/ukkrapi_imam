@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md rounded-2xl bg-white p-5 sm:p-6 shadow-lg">
        <h1 class="mb-2 text-2xl font-bold">Lupa Password?</h1>
        <p class="mb-6 text-sm text-slate-500">Masukkan email pemulihan akun Anda. Kami akan mengirimkan kode verifikasi OTP dan tautan untuk mengatur ulang kata sandi.</p>

        @if (session('status'))
            <p class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email Pemulihan</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="nama@smkn1bantul.sch.id" class="w-full rounded-xl border px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <button type="submit" class="w-full rounded-xl bg-sky-600 py-3 font-semibold text-white transition hover:bg-sky-700">Kirim Kode OTP & Tautan Reset</button>
        </form>

        <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-sky-700 hover:underline">Kembali ke login</a>
    </div>
@endsection