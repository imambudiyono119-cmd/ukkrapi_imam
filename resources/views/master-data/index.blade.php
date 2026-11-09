@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-xl font-bold">Tambah anggota</h2>
            <form method="POST" action="{{ route('master-data.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label for="fullname" class="mb-1 block text-sm font-medium">Nama lengkap</label>
                    <input id="fullname" name="fullname" value="{{ old('fullname') }}" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label for="username" class="mb-1 block text-sm font-medium">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label for="new-email" class="mb-1 block text-sm font-medium">Email pemulihan</label>
                    <input id="new-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label for="phone_number" class="mb-1 block text-sm font-medium">Nomor WhatsApp</label>
                    <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="628..." class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label for="role_id" class="mb-1 block text-sm font-medium">Peran</label>
                    <select id="role_id" name="role_id" required class="w-full rounded-lg border bg-white px-3 py-2">
                        <option value="">Pilih peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="new-password" class="mb-1 block text-sm font-medium">Password awal</label>
                    <input id="new-password" type="password" name="password" autocomplete="new-password" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium">Ulangi password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="w-full rounded-lg border px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Tambah anggota</button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
            <div class="border-b px-5 py-4">
                <h2 class="text-lg font-bold">Daftar anggota</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Username</th>
                            <th class="px-4 py-3 font-semibold">Peran</th>
                            <th class="min-w-64 px-4 py-3 font-semibold">Email pemulihan</th>
                            <th class="min-w-72 px-4 py-3 font-semibold">Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($members as $member)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $member->fullname }}</td>
                                <td class="px-4 py-3">{{ $member->username }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('master-data.role', $member) }}" class="flex min-w-48 gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role_id" aria-label="Peran {{ $member->fullname }}" required class="min-w-0 flex-1 rounded-lg border px-2 py-1.5">
                                            @if (! $roles->contains('id', $member->role_id))
                                                <option value="{{ $member->role_id }}" selected disabled>{{ $member->role->role_name }} (pilih peran baru)</option>
                                            @endif
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected($member->role_id === $role->id)>{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-50">Simpan</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('master-data.email', $member) }}" class="flex min-w-56 gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="email" name="email" value="{{ $member->email }}" aria-label="Email pemulihan {{ $member->fullname }}" required class="min-w-0 flex-1 rounded-lg border px-2 py-1.5">
                                        <button type="submit" title="Simpan email" class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-50">Simpan</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="mb-2 text-xs text-slate-500">Password saat ini tidak dapat dilihat.</p>
                                    <form method="POST" action="{{ route('master-data.password', $member) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="password" name="password" autocomplete="new-password" placeholder="Password baru" aria-label="Password baru {{ $member->fullname }}" required class="w-full rounded-lg border px-2 py-1.5">
                                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru" aria-label="Ulangi password baru {{ $member->fullname }}" required class="w-full rounded-lg border px-2 py-1.5">
                                        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 font-medium text-white hover:bg-slate-700">Atur ulang</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada anggota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection