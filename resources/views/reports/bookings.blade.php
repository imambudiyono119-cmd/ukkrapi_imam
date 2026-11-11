@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Periode: {{ $periodLabel }}</p>
                </div>
                <form method="GET" action="{{ route($periodType === 'daily' ? 'reports.daily' : 'reports.monthly') }}" class="flex flex-col sm:flex-row sm:items-end gap-2.5 w-full lg:w-auto">
                    <div class="w-full sm:w-auto">
                        <label for="report-period" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">
                            {{ $periodType === 'daily' ? 'Pilih tanggal' : 'Pilih bulan' }}
                        </label>
                        <input
                            id="report-period"
                            type="{{ $periodType === 'daily' ? 'date' : 'month' }}"
                            name="{{ $periodType === 'daily' ? 'date' : 'month' }}"
                            value="{{ $periodValue }}"
                            required
                            class="w-full sm:w-auto rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                    </div>
                    <div class="w-full sm:w-auto flex-1">
                        <label for="report-search" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Cari kegiatan, ruang, atau pemohon</label>
                        <input
                            id="report-search"
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            maxlength="100"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                            placeholder="Masukkan kata kunci..."
                        >
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto pt-1 sm:pt-0">
                        <button type="submit" class="w-full sm:w-auto rounded-xl bg-sky-700 hover:bg-sky-800 px-4 py-2 font-medium text-sm text-white transition text-center shadow-xs">Tampilkan</button>
                        @if ($periodType === 'monthly')
                            <button type="submit" name="download" value="excel" class="w-full sm:w-auto rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 font-medium text-sm text-white transition flex items-center justify-center gap-1.5 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Unduh Excel (Tabel Rapi)</span>
                            </button>
                            <button type="submit" name="download" value="print" class="w-full sm:w-auto rounded-xl border border-slate-700 px-4 py-2 font-medium text-sm text-slate-800 hover:bg-slate-100 transition text-center">
                                Cetak / PDF
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 sm:p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-slate-800">Peminjaman disetujui</h3>
                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                    {{ $events->total() }} peminjaman
                </span>
            </div>
            <div class="sm:hidden text-xs text-slate-500 mb-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
                <span>Geser tabel ke samping untuk melihat seluruh rincian</span>
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
