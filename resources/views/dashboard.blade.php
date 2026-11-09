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
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('events.decision', $pendingEvent) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="disetujui">
                                    <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white">Setujui</button>
                                </form>
                                <details>
                                    <summary class="cursor-pointer rounded-lg bg-red-700 px-3 py-2 text-sm font-medium text-white">Tolak</summary>
                                    <form method="POST" action="{{ route('events.decision', $pendingEvent) }}" class="mt-3 space-y-3 rounded-lg border border-red-200 p-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="ditolak">
                                        <div>
                                            <label for="rejection_reason_{{ $pendingEvent->id }}" class="mb-1 block text-sm font-medium">Alasan penolakan</label>
                                            <textarea id="rejection_reason_{{ $pendingEvent->id }}" name="rejection_reason" rows="3" maxlength="1000" required class="w-full rounded-lg border px-3 py-2"></textarea>
                                        </div>
                                        <button type="submit" class="rounded-lg bg-red-700 px-3 py-2 text-sm font-medium text-white">Kirim penolakan</button>
                                    </form>
                                </details>
                                <details class="w-full">
                                    <summary class="inline-block cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium">Pindahkan</summary>
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
