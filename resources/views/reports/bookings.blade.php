@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Periode: {{ $periodLabel }}</p>
                </div>
                <form method="GET" action="{{ route($periodType === 'daily' ? 'reports.daily' : 'reports.monthly') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="report-period" class="mb-1 block text-sm font-medium">
                            {{ $periodType === 'daily' ? 'Pilih tanggal' : 'Pilih bulan' }}
                        </label>
                        <input
                            id="report-period"
                            type="{{ $periodType === 'daily' ? 'date' : 'month' }}"
                            name="{{ $periodType === 'daily' ? 'date' : 'month' }}"
                            value="{{ $periodValue }}"
                            required
                            class="rounded-xl border px-3 py-2"
                        >
                    </div>
                    <div>
                        <label for="report-search" class="mb-1 block text-sm font-medium">Cari kegiatan, ruang, atau pemohon</label>
                        <input
                            id="report-search"
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            maxlength="100"
                            class="rounded-xl border px-3 py-2"
                            placeholder="Masukkan kata kunci"
                        >
                    </div>
                    <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 font-medium text-white">Tampilkan</button>
                    @if ($periodType === 'monthly')
                        <button type="submit" name="download" value="csv" class="rounded-xl border border-sky-700 px-4 py-2 font-medium text-sky-800">
                            Unduh CSV
                        </button>
                    @endif
                </form>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">Peminjaman disetujui</h3>
                <span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-medium text-sky-800">
                    {{ $events->total() }} peminjaman
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Kegiatan</th>
                            <th class="px-3 py-2">Ruang</th>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Waktu</th>
                            <th class="px-3 py-2">Pemohon</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($events as $event)
                            <tr>
                                <td class="px-3 py-3 font-medium">{{ $event->event_name }}</td>
                                <td class="px-3 py-3">{{ $event->room?->room_name ?? 'Ruang tidak tersedia' }}</td>
                                <td class="px-3 py-3">{{ \Carbon\CarbonImmutable::parse($event->date)->format('d-m-Y') }}</td>
                                <td class="px-3 py-3">{{ substr($event->start_time, 0, 5) }}–{{ substr($event->end_time, 0, 5) }}</td>
                                <td class="px-3 py-3">{{ $event->pic?->fullname ?? 'Pengguna tidak tersedia' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-slate-500">Tidak ada peminjaman disetujui pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        </section>
    </div>
@endsection
