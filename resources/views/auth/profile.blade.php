@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl p-4 shadow-sm">
        <h2 class="text-xl font-bold mb-4">Profil Saya</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                <input name="fullname" value="{{ old('fullname', $user->fullname) }}" class="w-full border rounded-xl px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nomor HP</label>
                <input name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full border rounded-xl px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <input type="password" name="password" class="w-full border rounded-xl px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full border rounded-xl px-3 py-2">
            </div>
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-xl">Simpan</button>
        </form>
    </div>
@endsection
