@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        @if ($eventNotifications->isNotEmpty())
            <section class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
                <h3 class="mb-3 font-semibold text-sky-900">Notifikasi jadwal</h3>
                @foreach ($eventNotifications as $notification)
                    <p class="mb-2 border-b border-sky-200 pb-2 text-sm text-sky-900 last:mb-0 last:border-0 last:pb-0">
                        {{ $notification->data['message'] ?? 'Ada pembaruan pada jadwal Anda.' }}
                    </p>
                @endforeach
            </section>
        @endif

        @if ($user->isAdmin())
            @if ($overdueComplaints->isNotEmpty())
                <section class="rounded-2xl border border-rose-300 bg-rose-50/70 p-4 sm:p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-100 text-rose-700 text-base font-bold">⚠️</span>
                            <div>
                                <h3 class="font-bold text-base text-rose-950">Peringatan Pengaduan Belum Ditangani (≥ 3 Hari)</h3>
                                <p class="text-xs text-rose-800">Terdapat {{ $overdueComplaints->count() }} pengaduan fasilitas yang belum diselesaikan oleh PIC selama lebih dari 3 hari.</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-900">
                            {{ $overdueComplaints->count() }} Tertunda
                        </span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($overdueComplaints as $overdue)
                            @php
                                $reportedDate = $overdue->reported_at ? \Carbon\CarbonImmutable::parse($overdue->reported_at) : $overdue->created_at;
                                $daysOld = (int) $reportedDate->diffInDays(now());
                                $pic = $overdue->room?->pic;
                                $waLink = \App\Helpers\WhatsAppLink::forComplaint($overdue);
                            @endphp
                            <div class="rounded-xl border border-rose-200 bg-white p-3.5 shadow-xs transition hover:border-rose-300 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-slate-900">{{ $overdue->room?->room_name ?? 'Ruang tidak diketahui' }}</span>
                                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800">
                                            Sudah {{ max(3, $daysOld) }} Hari Tertunda
                                        </span>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                                        Status: {{ $overdue->status_repair }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 leading-relaxed">
                                    {{ $overdue->description }}
                                </p>

                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-xs text-slate-500 border-t border-slate-100">
                                    <div>
                                        <span>Pelapor: <strong class="text-slate-700">{{ $overdue->reporter?->fullname ?? 'Tidak diketahui' }}</strong></span>
                                        &bull;
                                        <span>PIC Ruang: <strong class="text-slate-800">{{ $pic?->fullname ?? 'Belum ada PIC' }}</strong> ({{ $pic?->phone_number ?? '-' }})</span>
                                    </div>
                                    <div class="flex items-center gap-2 w-full sm:w-auto">
                                        @if ($waLink)
                                            <a
                                                href="{{ $waLink }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 text-xs font-medium transition shadow-xs"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.159.57 4.186 1.564 5.938l-1.564 5.89 6.046-1.586c1.705.933 3.666 1.458 5.754 1.458 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/>
                                                </svg>
                                                <span>Hubungi PIC via WhatsApp</span>
                                            </a>
                                        @endif
                                        <a
                                            href="{{ route('complaints.index', ['search' => $overdue->room?->room_name]) }}"
                                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-slate-300 hover:bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition"
                                        >
                                            Buka Pengaduan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="bg-white rounded-2xl p-4 shadow-sm">
                <h3 class="font-semibold mb-3">Notifikasi pengajuan jadwal</h3>
                @forelse ($pendingScheduleRequests as $pendingEvent)
                    <details class="mb-2 border-b pb-2 last:border-b-0">
                        <summary class="cursor-pointer font-medium">
                            {{ $pendingEvent->pic?->fullname ?? 'Pengguna' }} mengajukan “{{ $pendingEvent->event_name }}”
                            pada {{ \Carbon\CarbonImmutable::parse($pendingEvent->date)->format('d-m-Y') }} ({{ substr($pendingEvent->start_time, 0, 5) }} - {{ substr($pendingEvent->end_time, 0, 5) }})
                        </summary>
                        <div class="mt-3 space-y-3 pl-4">
                            <p class="text-sm text-slate-600">{{ $pendingEvent->room?->room_name }}{{ $pendingEvent->room?->location ? ' - '.$pendingEvent->room->location : '' }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('events.decision', $pendingEvent) }}" class="w-full sm:w-auto">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="disetujui">
                                    <button type="submit" class="w-full sm:w-auto rounded-lg bg-emerald-700 px-3.5 py-2 text-sm font-medium text-white text-center hover:bg-emerald-800 transition">Setujui</button>
                                </form>
                                <details class="w-full sm:w-auto">
                                    <summary class="w-full sm:w-auto text-center cursor-pointer rounded-lg bg-red-700 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-800 transition">Tolak</summary>
                                    <form method="POST" action="{{ route('events.decision', $pendingEvent) }}" class="mt-3 space-y-3 rounded-lg border border-red-200 p-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="ditolak">
                                        <div>
                                            <label for="rejection_reason_{{ $pendingEvent->id }}" class="mb-1 block text-sm font-medium">Alasan penolakan</label>
                                            <textarea id="rejection_reason_{{ $pendingEvent->id }}" name="rejection_reason" rows="3" maxlength="1000" required class="w-full rounded-lg border px-3 py-2"></textarea>
                                        </div>
                                        <button type="submit" class="w-full sm:w-auto rounded-lg bg-red-700 px-3 py-2 text-sm font-medium text-white">Kirim penolakan</button>
                                    </form>
                                </details>
                                <details class="w-full">
                                    <summary class="w-full sm:w-auto text-center inline-block cursor-pointer rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium hover:bg-slate-50 transition">Pindahkan</summary>
                                    <form method="POST" action="{{ route('events.update', $pendingEvent) }}" class="mt-3 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 p-3 sm:grid-cols-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="event_name" value="{{ $pendingEvent->event_name }}">
                                        <div>
                                            <label class="mb-1 block text-sm">Ruang baru</label>
                                            <select name="room_id" class="w-full rounded-lg border px-3 py-2" required>
                                                @foreach ($moveRooms as $roomOption)
                                                    <option value="{{ $roomOption->id }}" @selected($roomOption->id === $pendingEvent->room_id)>{{ $roomOption->room_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm">Tanggal baru</label>
                                            <input type="date" name="date" value="{{ $pendingEvent->date }}" class="w-full rounded-lg border px-3 py-2" required>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="mb-1 block text-sm">Pilih Sesi Sekolah</label>
                                            <select onchange="if(this.value){ const parts = this.value.split('-'); this.form.start_time.value = parts[0]; this.form.end_time.value = parts[1]; }" class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                                <option value="">-- Pilih Sesi Standar --</option>
                                                <option value="07:30-08:30">Sesi 1 (07:30 - 08:30) • JP 1-2</option>
                                                <option value="08:30-10:00">Sesi 2 (08:30 - 10:00) • JP 3-4</option>
                                                <option value="10:15-11:45">Sesi 3 (10:15 - 11:45) • JP 5-6</option>
                                                <option value="12:30-14:00">Sesi 4 (12:30 - 14:00) • JP 7-8</option>
                                                <option value="14:00-15:30">Sesi 5 (14:00 - 15:30) • JP 9-10 (Pulang Sekolah)</option>
                                                <option value="07:30-15:30">Sesi Seharian Penuh Sekolah (07:30 - 15:30)</option>
                                                <option value="15:30-17:30">Sesi Ekstrakurikuler / Sore (15:30 - 17:30)</option>
                                                <option value="18:30-22:00">Sesi Malam Hari (18:30 - 22:00)</option>
                                                <option value="00:00-23:59">Sesi Peminjaman 24 Jam Penuh (00:00 - 23:59)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm">Mulai</label>
                                            <input type="time" name="start_time" value="{{ substr($pendingEvent->start_time, 0, 5) }}" class="w-full rounded-lg border px-3 py-2" required>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm">Selesai</label>
                                            <input type="time" name="end_time" value="{{ substr($pendingEvent->end_time, 0, 5) }}" class="w-full rounded-lg border px-3 py-2" required>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="mb-1 block text-sm">Alasan pemindahan</label>
                                            <input type="text" name="move_reason" class="w-full rounded-lg border px-3 py-2" maxlength="1000" required>
                                        </div>
                                        <button type="submit" class="rounded-lg bg-sky-700 px-3 py-2 text-sm font-medium text-white sm:col-span-2">Simpan pemindahan</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('events.destroy', $pendingEvent) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan kegiatan ini?');" class="w-full sm:w-auto">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full sm:w-auto rounded-lg border border-rose-300 bg-rose-50 px-3.5 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 transition text-center">Batalkan</button>
                                </form>
                            </div>
                        </div>
                    </details>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada pengajuan jadwal yang menunggu.</p>
                @endforelse
            </section>
        @endif

    </div>
@endsection
