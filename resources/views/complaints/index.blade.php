@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        {{-- Form Pengaduan: Ditampilkan HANYA untuk pengguna yang BUKAN PIC Ruang --}}
        @if (! auth()->user()->isPic())
            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
                <h2 class="text-lg sm:text-xl font-bold mb-1 text-slate-800">Buat Laporan Pengaduan</h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-4">Laporkan kendala atau kerusakan fasilitas ruang sekolah agar segera ditindaklanjuti.</p>

                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-4">
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
                            <label class="block text-sm font-medium mb-1 text-slate-700">Ruang / Fasilitas <span class="text-rose-500">*</span></label>
                            <select name="room_id" class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500" required>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>{{ $room->room_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Deskripsi Kerusakan <span class="text-rose-500">*</span></label>
                        <textarea name="description" class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500" rows="3" placeholder="Jelaskan detail kerusakan atau masalah yang ditemukan..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700">Foto Bukti (Gambar)</label>
                            <input type="file" name="photo" accept="image/*" class="w-full border rounded-xl px-3 py-2 text-sm bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                            <p class="mt-1 text-[11px] text-slate-500">Format: JPG, PNG, WEBP (Maks 10MB)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700">Dokumen / Berkas Pendukung (Opsional)</label>
                            <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.csv" class="w-full border rounded-xl px-3 py-2 text-sm bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <p class="mt-1 text-[11px] text-slate-500">Format: PDF, Word, Excel, ZIP (Maks 20MB)</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition text-center shadow-xs">
                        Kirim Pengaduan
                    </button>
                </form>
            </div>
        @else
            {{-- Khusus PIC Ruang: Info Panel Penanganan --}}
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:p-5 text-sky-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-sky-100 text-sky-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-sky-950">Panel Penanganan PIC Ruang</h3>
                        <p class="text-xs sm:text-sm text-sky-800 mt-0.5 leading-relaxed">
                            Sebagai PIC Ruang, Anda bertugas menangani dan menindaklanjuti pengaduan yang disampaikan pada ruang yang Anda kelola. Anda dapat memperbarui status, memberi catatan perbaikan, serta melampirkan foto hasil perbaikan (dapat langsung membuka kamera) atau dokumen pendukung.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filter & Daftar Riwayat Pengaduan --}}
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">
                        @if (auth()->user()->isAdmin())
                            Seluruh Pengaduan Fasilitas
                        @elseif (auth()->user()->isPic())
                            Pengaduan Masuk (Ruang Binaan PIC)
                        @else
                            Riwayat Pengaduan Saya
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500">
                        @if (auth()->user()->isAdmin())
                            Menampilkan seluruh riwayat pengaduan yang diajukan oleh pengguna sekolah.
                        @elseif (auth()->user()->isPic())
                            Menampilkan pengaduan yang disampaikan terkait ruang yang Anda bina.
                        @else
                            Menampilkan daftar pengaduan yang telah Anda sampaikan.
                        @endif
                    </p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    Total: {{ $complaints->total() }} Laporan
                </span>
            </div>

            <form method="GET" action="{{ route('complaints.index') }}" class="mb-5 flex flex-col sm:flex-row sm:items-end gap-2.5">
                @if ($selectedEvent)
                    <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">
                @endif
                <div class="flex-1">
                    <label for="complaint-search" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Cari pengaduan, ruang, pemohon, atau kegiatan</label>
                    <input
                        id="complaint-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        maxlength="100"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Masukkan kata kunci pencarian..."
                    >
                </div>
                <div class="w-full sm:w-auto">
                    <label for="complaint-status" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Status penanganan</label>
                    <select id="complaint-status" name="status" class="w-full sm:w-auto rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                        <option value="{{ \App\Models\Complaint::STATUS_PENDING }}" @selected($status === \App\Models\Complaint::STATUS_PENDING)>Belum Diperbaiki</option>
                        <option value="{{ \App\Models\Complaint::STATUS_PARTS }}" @selected($status === \App\Models\Complaint::STATUS_PARTS)>Menunggu Spare Part</option>
                        <option value="{{ \App\Models\Complaint::STATUS_DONE }}" @selected($status === \App\Models\Complaint::STATUS_DONE)>Sudah Diperbaiki</option>
                        <option value="all" @selected($status === 'all')>Semua</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto rounded-xl bg-sky-700 hover:bg-sky-800 px-5 py-2 font-medium text-sm text-white transition text-center">Terapkan</button>
            </form>

            <div class="space-y-4">
                @forelse ($complaints as $complaint)
                    <div class="border border-slate-200 rounded-2xl p-4 hover:border-slate-300 transition bg-white shadow-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-base text-slate-800">{{ $complaint->room?->room_name ?? 'Ruang tidak ditemukan' }}</span>
                                @if ($complaint->room?->location)
                                    <span class="text-xs text-slate-500">({{ $complaint->room->location }})</span>
                                @endif
                            </div>
                            <div>
                                @if ($complaint->status_repair === \App\Models\Complaint::STATUS_DONE)
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-emerald-100 text-emerald-800">
                                        ✓ {{ $complaint->status_repair }}
                                    </span>
                                @elseif ($complaint->status_repair === \App\Models\Complaint::STATUS_PARTS)
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-amber-100 text-amber-800">
                                        ⏳ {{ $complaint->status_repair }}
                                    </span>
                                @else
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-rose-100 text-rose-800">
                                        • {{ $complaint->status_repair }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 space-y-0.5 mb-2">
                            <p>
                                <strong>Pelapor:</strong> {{ $complaint->reporter?->fullname ?? 'Tidak diketahui' }}
                                @if ($complaint->reporter?->role)
                                    <span class="text-[11px] text-slate-400">({{ $complaint->reporter->role->role_name }})</span>
                                @endif
                                &bull;
                                <span>{{ $complaint->reported_at ? \Carbon\CarbonImmutable::parse($complaint->reported_at)->translatedFormat('d F Y, H:i') : $complaint->created_at->format('d-m-Y H:i') }} WIB</span>
                            </p>
                            @if ($complaint->event)
                                <p class="text-sky-700">
                                    <strong>Kegiatan Peminjaman:</strong> {{ $complaint->event->event_name }} &bull; {{ \Carbon\CarbonImmutable::parse($complaint->event->date)->format('d-m-Y') }}
                                </p>
                            @endif
                        </div>

                        <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 rounded-xl p-3 border border-slate-100">
                            {{ $complaint->description }}
                        </p>

                        {{-- Lampiran Foto & Dokumen Pelapor --}}
                        @if ($complaint->photo_path || $complaint->file_path)
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if ($complaint->photo_path)
                                    <div>
                                        <p class="text-xs font-semibold text-slate-600 mb-1">Foto Bukti:</p>
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_path) }}" target="_blank" class="block group">
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_path) }}"
                                                alt="Foto bukti pengaduan"
                                                class="max-h-48 rounded-xl object-contain border border-slate-200 group-hover:opacity-90 transition"
                                            >
                                        </a>
                                    </div>
                                @endif

                                @if ($complaint->file_path)
                                    <div>
                                        <p class="text-xs font-semibold text-slate-600 mb-1">Dokumen Berkas:</p>
                                        <a
                                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->file_path) }}"
                                            target="_blank"
                                            download
                                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-medium border border-slate-300 transition"
                                        >
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Unduh Dokumen Pelapor
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Hasil Tindak Lanjut Petugas (Jika Sudah Pernah Diupdate PIC) --}}
                        @if ($complaint->handler_note || $complaint->handler_photo_path || $complaint->handler_file_path || $complaint->handler)
                            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3 text-xs text-emerald-950">
                                <div class="flex items-center gap-1.5 font-bold text-emerald-800 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Hasil Penanganan Petugas PIC:
                                    @if ($complaint->handler)
                                        <span class="font-semibold">{{ $complaint->handler->fullname }}</span>
                                    @endif
                                </div>
                                @if ($complaint->handler_note)
                                    <p class="text-slate-700 mt-1 leading-relaxed">
                                        {{ $complaint->handler_note }}
                                    </p>
                                @endif
                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    @if ($complaint->handler_photo_path)
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-600 mb-1">Foto Bukti Perbaikan:</p>
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->handler_photo_path) }}" target="_blank">
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->handler_photo_path) }}"
                                                    alt="Foto hasil perbaikan"
                                                    class="max-h-36 rounded-lg object-contain border border-emerald-200"
                                                >
                                            </a>
                                        </div>
                                    @endif
                                    @if ($complaint->handler_file_path)
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-600 mb-1">Berkas Tindak Lanjut:</p>
                                            <a
                                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->handler_file_path) }}"
                                                target="_blank"
                                                download
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-emerald-300 text-emerald-900 text-xs font-medium hover:bg-emerald-100 transition"
                                            >
                                                Unduh Berkas Perbaikan
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Form Update Penanganan untuk PIC Ruang dan Waka/Admin --}}
                        @if (auth()->user()->isAdmin() || (auth()->user()->isPic() && $complaint->room && $complaint->room->pic_id === auth()->id()))
                            <details class="mt-3 border-t border-slate-100 pt-3 group">
                                <summary class="cursor-pointer text-xs font-semibold text-sky-700 hover:text-sky-800 list-none flex items-center justify-between">
                                    <span>⚙ Perbarui Status &amp; Unggah Bukti Penanganan</span>
                                    <span class="text-slate-400 group-open:rotate-180 transition transform text-[10px]">&#9660;</span>
                                </summary>
                                <form method="POST" action="{{ route('complaints.updateStatus', $complaint) }}" enctype="multipart/form-data" class="mt-3 space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block font-medium mb-1 text-slate-700">Status Perbaikan</label>
                                        <select name="status_repair" class="border rounded-lg px-2.5 py-1.5 w-full bg-white text-xs focus:ring-1 focus:ring-sky-500">
                                            @foreach (['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'] as $st)
                                                <option value="{{ $st }}" @selected($complaint->status_repair === $st)>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1 text-slate-700">Catatan / Uraian Perbaikan</label>
                                        <textarea name="handler_note" rows="2" class="border rounded-lg px-2.5 py-1.5 w-full bg-white text-xs focus:ring-1 focus:ring-sky-500" placeholder="Contoh: Lampu telah diganti dengan yang baru, saklar diperbaiki.">{{ old('handler_note', $complaint->handler_note) }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block font-medium mb-1 text-slate-700">Foto Bukti Perbaikan (Buka Kamera / Galeri)</label>
                                            <input type="file" name="handler_photo" accept="image/*" capture="environment" class="w-full border rounded-lg px-2 py-1 bg-white text-xs">
                                            <p class="text-[10px] text-slate-500 mt-0.5">Pada HP akan langsung membuka kamera.</p>
                                        </div>
                                        <div>
                                            <label class="block font-medium mb-1 text-slate-700">Berkas / Dokumen Pendukung (Opsional)</label>
                                            <input type="file" name="handler_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" class="w-full border rounded-lg px-2 py-1 bg-white text-xs">
                                        </div>
                                    </div>
                                    <div class="flex justify-end pt-1">
                                        <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg font-medium text-xs transition text-center shadow-xs">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-6 text-center">Belum ada pengaduan yang terdaftar.</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $complaints->links() }}</div>
        </div>
    </div>
@endsection
