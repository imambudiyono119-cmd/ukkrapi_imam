@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl p-4 shadow-sm">
        <h2 class="text-xl font-bold mb-4">Pengaduan</h2>
        <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-4 mb-6">
            @csrf
            @if ($selectedEvent)
                <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">
                <input type="hidden" name="room_id" value="{{ $selectedEvent->room_id }}">
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                    Pengaduan terkait peminjaman <strong>{{ $selectedEvent->event_name }}</strong>
                    di {{ $selectedEvent->room?->room_name }} pada {{ \Carbon\CarbonImmutable::parse($selectedEvent->date)->format('d-m-Y') }}.
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium mb-1">Ruang</label>
                    <select name="room_id" class="w-full border rounded-xl px-3 py-2" required>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>{{ $room->room_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded-xl px-3 py-2" rows="4" required>{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Foto Bukti</label>
                <input type="file" name="photo" accept="image/*" class="w-full border rounded-xl px-3 py-2">
            </div>
            <button class="bg-rose-600 text-white px-4 py-2 rounded-xl">Kirim Pengaduan</button>
        </form>

        <form method="GET" action="{{ route('complaints.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
            @if ($selectedEvent)
                <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">
            @endif
            <div class="min-w-60 flex-1">
                <label for="complaint-search" class="mb-1 block text-sm font-medium">Cari pengaduan, ruang, pemohon, atau kegiatan</label>
                <input
                    id="complaint-search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    maxlength="100"
                    class="w-full rounded-xl border px-3 py-2"
                    placeholder="Masukkan kata kunci"
                >
            </div>
            <div>
                <label for="complaint-status" class="mb-1 block text-sm font-medium">Status penanganan</label>
                <select id="complaint-status" name="status" class="rounded-xl border px-3 py-2">
                    <option value="{{ \App\Models\Complaint::STATUS_PENDING }}" @selected($status === \App\Models\Complaint::STATUS_PENDING)>Belum Diperbaiki</option>
                    <option value="{{ \App\Models\Complaint::STATUS_PARTS }}" @selected($status === \App\Models\Complaint::STATUS_PARTS)>Menunggu Spare Part</option>
                    <option value="{{ \App\Models\Complaint::STATUS_DONE }}" @selected($status === \App\Models\Complaint::STATUS_DONE)>Sudah Diperbaiki</option>
                    <option value="all" @selected($status === 'all')>Semua</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 font-medium text-white">Terapkan</button>
        </form>

        <div class="space-y-3">
            @forelse ($complaints as $complaint)
                <div class="border rounded-xl p-3">
                    <div class="flex justify-between">
                        <strong>{{ $complaint->room?->room_name }}</strong>
                        <span class="text-xs bg-slate-200 px-2 py-1 rounded">{{ $complaint->status_repair }}</span>
                    </div>
                    @if ($complaint->event)
                        <p class="mt-1 text-xs text-slate-500">
                            Peminjaman: {{ $complaint->event->event_name }} • {{ \Carbon\CarbonImmutable::parse($complaint->event->date)->format('d-m-Y') }}
                        </p>
                    @endif
                    <p class="text-sm mt-2">{{ $complaint->description }}</p>
                    @if ($complaint->photo_path)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_path) }}"
                            alt="Foto bukti pengaduan"
                            class="mt-3 max-h-64 rounded-lg object-contain"
                        >
                    @endif

                    @if (auth()->user()->isAdmin() || auth()->user()->isPic())
                        <form method="POST" action="{{ route('complaints.updateStatus', $complaint) }}" class="mt-3 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status_repair" class="border rounded-xl px-2 py-2 flex-1">
                                @foreach (['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'] as $status)
                                    <option value="{{ $status }}" @selected($complaint->status_repair === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button class="bg-sky-600 text-white px-3 py-2 rounded-xl">Update</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pengaduan.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $complaints->links() }}</div>
    </div>
@endsection
