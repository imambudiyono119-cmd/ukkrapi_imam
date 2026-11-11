@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm max-w-2xl">
        <h2 class="text-xl font-bold mb-1 text-slate-800">Profil Saya</h2>
        <p class="text-xs sm:text-sm text-slate-500 mb-5">Perbarui informasi nama lengkap, nomor HP, atau ubah kata sandi akun Anda.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700">Nama Lengkap</label>
                <input name="fullname" value="{{ old('fullname', $user->fullname) }}" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700">Nomor HP / WhatsApp</label>
                <input name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Contoh: 628123456789" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Ubah Password (Kosongkan jika tidak ingin mengubah)</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition text-center shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
