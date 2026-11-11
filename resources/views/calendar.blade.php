@extends('layouts.app')

@php
    $isAdmin = auth()->user()?->isAdmin() ?? false;
    $canBookHoliday = $isAdmin || (auth()->user()?->isTeacher() ?? false);
    $today = today()->toDateString();
    $roomOptions = \Illuminate\Support\Facades\Schema::hasTable('rooms')
        ? \App\Models\Room::query()->get()
        : collect();
    $defaultRoomId = \Illuminate\Support\Facades\Schema::hasTable('rooms')
        ? (\App\Models\Room::query()->value('id') ?? '')
        : '';
@endphp

@section('content')
    <section class="mb-4 overflow-hidden rounded-2xl bg-white shadow-sm" data-principal-welcome>
        <div class="grid items-start md:grid-cols-[minmax(12rem,0.7fr)_minmax(0,2fr)]">
            <div class="bg-slate-100">
                <img
                    src="{{ asset('images/kepala-sekolah.jpeg') }}"
                    alt="Raharjo, S.IP, M.Pd., Kepala Sekolah SMKN 1 Bantul"
                    class="h-48 w-full object-cover object-[center_30%] sm:h-52 md:h-60"
                >
            </div>
            <div class="space-y-2 p-4 sm:p-5">
                <header>
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Sambutan Kepala Sekolah</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">Raharjo, S.IP, M.Pd</h2>
                    <p class="text-sm text-slate-600">Kepala Sekolah SMKN 1 Bantul</p>
                </header>
                <details class="text-xs leading-5 text-slate-700 sm:text-sm sm:leading-6">
                    <summary class="w-fit cursor-pointer font-medium text-sky-700 hover:text-sky-800">Baca sambutan lengkap</summary>
                    <div class="mt-2 max-h-32 space-y-2 overflow-y-auto overscroll-contain pr-2 sm:max-h-40" tabindex="0" aria-label="Teks sambutan lengkap, gulir untuk membaca">
                        <p>Assalamualaikum warahmatullahi wabarakatuh, Salam sejahtera bagi kita semua. Saya, Raharjo, M.Pd., Kepala SMK Negeri 1 Bantul, dengan bangga menyampaikan visi dan misi sekolah kami, yaitu mencetak lulusan yang unggul, berkompeten, dan siap bersaing di dunia global. Visi ini kami wujudkan melalui pendidikan yang berbasis pada penguatan karakter, keterampilan, dan penguasaan teknologi. Kami percaya bahwa dengan pendidikan yang berkualitas, kami dapat menyiapkan generasi yang siap menghadapi tantangan masa depan, baik di dunia kerja, wirausaha, maupun pendidikan tinggi.</p>
                        <p>Di SMK Negeri 1 Bantul, kami menerapkan pembelajaran yang memanusiakan hubungan, memahami konsep, membangun keberlanjutan, memilih tantangan, dan memberdayakan konteks. Dengan pendekatan ini, kami berupaya menciptakan siswa yang tidak hanya cerdas secara akademik, tetapi juga memiliki karakter yang sesuai dengan Profil Pelajar Pancasila. Kami ingin siswa mampu berkolaborasi, berinovasi, dan berkontribusi positif dalam masyarakat. Harapan besar kami adalah lulusan SMK Negeri 1 Bantul menjadi generasi yang siap kerja di dunia industri, siap berwirausaha dengan ide-ide kreatifnya, serta siap melanjutkan pendidikan ke jenjang yang lebih tinggi. Kami percaya, dengan dukungan semua pihak, cita-cita ini dapat terwujud, dan lulusan kami akan menjadi kebanggaan bangsa.</p>
                        <p class="font-medium text-slate-900">Teruslah belajar, berinovasi, dan berkontribusi untuk masa depan yang lebih baik. Wassalamualaikum warahmatullahi wabarakatuh.</p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <style>
        #calendar .fc-daygrid-day.national-holiday-cell {
            background-color: #fee2e2;
        }

        #calendar .fc-daygrid-day.national-holiday-cell .fc-daygrid-day-number {
            color: #b91c1c;
        }

        #calendar .fc-daygrid-day.weekend-cell:not(.national-holiday-cell) {
            background-color: #fff1f2;
        }

        #calendar .fc-daygrid-day.weekend-cell .fc-daygrid-day-number {
            color: #be123c;
        }

        #calendar .calendar-day-marker {
            display: block;
            overflow: hidden;
            padding: 0 0.25rem 0.125rem;
            color: #be123c;
            font-size: 0.625rem;
            line-height: 1rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #calendar .national-holiday-cell .calendar-day-marker {
            font-weight: 600;
            color: #b91c1c;
        }

        #calendar .day-agenda-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-inline-start: 0.25rem;
            border-radius: 0.375rem;
            padding: 0.125rem;
            color: #0369a1;
        }

        #calendar .day-agenda-button:hover,
        #calendar .day-agenda-button:focus-visible {
            background-color: #e0f2fe;
        }

        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.65rem;
                align-items: center;
            }
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.35rem;
            }
            .fc .fc-toolbar-title {
                font-size: 1.15rem !important;
                text-align: center;
            }
            .fc .fc-button {
                padding: 0.35rem 0.65rem !important;
                font-size: 0.8rem !important;
                border-radius: 0.5rem !important;
            }
            .fc .fc-col-header-cell-cushion {
                font-size: 0.75rem !important;
                padding: 4px 2px !important;
            }
            .fc .fc-daygrid-day-frame {
                min-height: 52px !important;
            }
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem !important;
                padding: 2px 4px !important;
            }
            .fc .fc-daygrid-event {
                font-size: 0.7rem !important;
                margin-top: 1px !important;
                margin-bottom: 1px !important;
            }
        }
    </style>
    <div class="bg-white rounded-2xl p-3.5 sm:p-5 shadow-sm">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <form id="calendar-search-form" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-end gap-2">
                <div class="flex-1">
                    <label for="calendar-search" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Cari kegiatan, ruang, atau pemohon</label>
                    <input
                        id="calendar-search"
                        type="search"
                        maxlength="100"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Masukkan kata kunci pencarian..."
                    >
                </div>
                <button type="submit" class="w-full sm:w-auto rounded-xl bg-sky-700 hover:bg-sky-800 px-5 py-2 text-sm font-medium text-white transition">Cari</button>
            </form>
            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isGuru()))
                <button type="button" id="addEventButton" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm transition text-center shadow-xs">
                    {{ auth()->user()->isAdmin() ? '+ Tambah Kegiatan' : '+ Ajukan Kegiatan' }}
                </button>
            @endif
        </div>

        <!-- Legend Highlight Peminjam & Info H-3 Jam -->
        <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                <span class="font-bold text-slate-700">Highlight Peminjam:</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-indigo-600 shrink-0"></span> Waka/Admin Langsung</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-sky-600 shrink-0"></span> Guru</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-emerald-600 shrink-0"></span> Siswa / Organisasi</span>
            </div>
            <div class="text-slate-600 font-medium">
                <span class="text-amber-700 font-bold">ℹ Aturan:</span> Peminjaman minimal diajukan <strong class="text-slate-800">H-3 jam</strong> sebelum kegiatan.
            </div>
        </div>

        <div id="calendar"></div>
    </div>

    @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isGuru()))
        <div id="eventModal" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center z-50 p-3 sm:p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg p-4 sm:p-6 max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="eventModalTitle" class="font-bold text-lg text-slate-800">Tambah kegiatan</h3>
                    <button type="button" data-close-modal class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" aria-label="Tutup form">✕</button>
                </div>
                <form id="eventForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama agenda</label>
                            <input name="event_name" required placeholder="Contoh: Pembelajaran Praktik, Rapat OSIS" class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Ruang / Fasilitas</label>
                            <select name="room_id" required class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                                @foreach ($roomOptions as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Tanggal</label>
                                <input type="date" name="date" id="event_date_input" min="{{ $today }}" required class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                            </div>
                            @if (auth()->user()->isAdmin())
                                <div data-status-field>
                                    <label class="block text-sm font-medium mb-1">Status persetujuan</label>
                                    <select name="status" class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                                        <option value="disetujui">Disetujui</option>
                                        <option value="diajukan">Menunggu persetujuan</option>
                                    </select>
                                </div>
                            @else
                                <p class="col-span-1 sm:col-span-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    Agenda akan menunggu persetujuan Waka sebelum disetujui.
                                </p>
                            @endif
                        </div>

                        <!-- Pilihan Sesi Sekolah -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium">Pilih Sesi Sekolah</label>
                                @if (auth()->user()->isGuru() || auth()->user()->isAdmin())
                                    <span class="text-[11px] font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md">Guru: Bebas Malam / 24 Jam</span>
                                @endif
                            </div>
                            <select id="session_select" class="w-full border rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-sky-500">
                                <option value="">-- Pilih Sesi Pembelajaran / Kegiatan --</option>
                                <optgroup label="Sesi Jam Sekolah Reguler">
                                    <option value="07:30-08:30" data-start="07:30" data-end="08:30">Sesi 1 (07:30 - 08:30) • JP 1-2</option>
                                    <option value="08:30-10:00" data-start="08:30" data-end="10:00">Sesi 2 (08:30 - 10:00) • JP 3-4</option>
                                    <option value="10:15-11:45" data-start="10:15" data-end="11:45">Sesi 3 (10:15 - 11:45) • JP 5-6</option>
                                    <option value="12:30-14:00" data-start="12:30" data-end="14:00">Sesi 4 (12:30 - 14:00) • JP 7-8</option>
                                    <option value="14:00-15:30" data-start="14:00" data-end="15:30">Sesi 5 (14:00 - 15:30) • JP 9-10 (Pulang Sekolah)</option>
                                    <option value="07:30-15:30" data-start="07:30" data-end="15:30">Sesi Seharian Penuh Sekolah (07:30 - 15:30)</option>
                                    <option value="15:30-17:30" data-start="15:30" data-end="17:30">Sesi Ekstrakurikuler / Sore (15:30 - 17:30)</option>
                                </optgroup>
                                @if (auth()->user()->isGuru() || auth()->user()->isAdmin())
                                    <optgroup label="Khusus Guru & Waka (Malam & 24 Jam)">
                                        <option value="18:30-22:00" data-start="18:30" data-end="22:00">Sesi Malam Hari (18:30 - 22:00)</option>
                                        <option value="00:00-23:59" data-start="00:00" data-end="23:59">Sesi Peminjaman 24 Jam Penuh (00:00 - 23:59)</option>
                                    </optgroup>
                                @endif
                                <option value="custom">Kustom (Tentukan Jam Mandiri)</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">
                                @if (auth()->user()->isGuru() || auth()->user()->isAdmin())
                                    ✓ Guru &amp; Waka dapat meminjam ruang di malam hari atau hingga 24 jam penuh.
                                @else
                                    ℹ Peminjaman siswa/organisasi dibatasi pada jam operasional sekolah (07:00 - 17:30).
                                @endif
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Jam Mulai</label>
                                <input type="time" name="start_time" id="start_time_input" required class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Jam Selesai</label>
                                <input type="time" name="end_time" id="end_time_input" required class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500">
                            </div>
                        </div>

                        <div id="h3_warning_box" class="hidden rounded-xl border border-rose-300 bg-rose-50 p-2.5 text-xs text-rose-800">
                            ⚠️ <strong>Peringatan H-3 Jam:</strong> Peminjaman untuk hari ini harus diajukan minimal 3 jam sebelum waktu mulai kegiatan (tidak bisa mendadak).
                        </div>

                        <button type="submit" id="eventSubmitButton" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-medium py-3 rounded-xl transition">
                            {{ auth()->user()->isAdmin() ? 'Simpan perubahan' : 'Ajukan kegiatan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div id="agendaModal" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center z-50 p-3 sm:p-4" aria-hidden="true">
        <section class="w-full max-w-xl rounded-2xl bg-white p-4 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="agendaModalTitle">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 id="agendaModalTitle" class="text-lg font-bold text-slate-800">Agenda hari ini</h3>
                <button type="button" data-close-agenda class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" aria-label="Tutup agenda">✕</button>
            </div>
            <div id="agendaList" class="max-h-[65vh] space-y-3 overflow-y-auto"></div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const eventModal = document.getElementById('eventModal');
            const agendaModal = document.getElementById('agendaModal');
            const agendaModalTitle = document.getElementById('agendaModalTitle');
            const agendaList = document.getElementById('agendaList');
            const eventForm = document.getElementById('eventForm');
            const calendarSearchForm = document.getElementById('calendar-search-form');
            const calendarSearch = document.getElementById('calendar-search');
            const modalTitle = document.getElementById('eventModalTitle');
            const addEventButton = document.getElementById('addEventButton');
            const today = @json($today);
            const canBookHoliday = @json($canBookHoliday);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                events: {
                    url: '{{ route('api.events') }}',
                    extraParams: function () {
                        return { search: calendarSearch.value.trim() };
                    }
                },
                eventSources: ['{{ route('api.holidays') }}'],
                eventDisplay: 'block',
                eventOrder: 'start,-duration,title',
                dayMaxEvents: 1,
                moreLinkText: function(n) {
                    return '+' + n + ' kegiatan lainnya (Klik untuk lihat)';
                },
                moreLinkClick: function (arg) {
                    const dateStr = FullCalendar.formatDate(arg.date, { year: 'numeric', month: '2-digit', day: '2-digit' }).split('/').reverse().join('-');
                    const dayEvents = calendar.getEvents().filter(function (event) {
                        return event.startStr.slice(0, 10) === dateStr && event.extendedProps.type !== 'national_holiday';
                    });
                    showDayAgenda(dateStr, dayEvents);
                    return false;
                },
                editable: false,
                eventsSet: renderDateMarkers,
                datesSet: function () {
                    window.requestAnimationFrame(renderDateMarkers);
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps.type === 'national_holiday') {
                        return;
                    }

                    const eventEnd = info.event.end || info.event.start;
                    const isPastEvent = eventEnd < new Date();
                    let textColor = '#000';

                    if (info.event.extendedProps.status === 'diajukan') {
                        textColor = '#a16207';
                    } else if (isPastEvent) {
                        textColor = '#15803d';
                    }

                    const bookerType = info.event.extendedProps.booker_type || 'guru';
                    let bgColor = '#0284c7';
                    let borderColor = '#0369a1';

                    if (bookerType === 'waka') {
                        bgColor = '#4f46e5';
                        borderColor = '#3730a3';
                    } else if (bookerType === 'siswa') {
                        bgColor = '#059669';
                        borderColor = '#047857';
                    }

                    info.el.style.setProperty('--fc-event-bg-color', bgColor);
                    info.el.style.setProperty('--fc-event-border-color', borderColor);
                    info.el.style.setProperty('--fc-event-text-color', '#ffffff');
                    info.el.classList.add('rounded-lg', 'shadow-xs', 'px-1.5', 'py-1', 'cursor-pointer');
                },
                eventContent: function(info) {
                    const details = document.createElement('div');
                    const headerRow = document.createElement('div');
                    const badge = document.createElement('span');
                    const title = document.createElement('strong');
                    const time = document.createElement('span');
                    const place = document.createElement('span');
                    const status = document.createElement('span');
                    const props = info.event.extendedProps;
                    const roomLocation = [props.room_name, props.room_location].filter(Boolean).join(', ');

                    if (props.type === 'national_holiday') {
                        return { domNodes: [] };
                    }

                    details.className = 'flex flex-col gap-0.5 overflow-hidden text-[11px] leading-tight text-white p-1';
                    headerRow.className = 'flex items-center gap-1.5 mb-0.5';

                    badge.className = 'w-fit text-[9px] uppercase px-1.5 py-0.5 rounded font-bold tracking-wider bg-black/25 text-white';
                    badge.textContent = props.booker_role || 'Guru';
                    headerRow.append(badge);

                    title.className = 'font-semibold truncate';
                    title.textContent = info.event.title;
                    headerRow.append(title);

                    time.className = 'text-[10px] opacity-90';
                    time.textContent = 'Pukul ' + (props.start_time || '').slice(0, 5) + ' - ' + (props.end_time || '').slice(0, 5);
                    place.className = 'text-[10px] opacity-90 truncate';
                    place.textContent = 'Tempat: ' + (roomLocation || 'Belum ditentukan');

                    details.append(headerRow, time, place);

                    if (props.status === 'diajukan') {
                        status.className = 'text-[10px] text-amber-200 font-semibold';
                        status.textContent = '⏳ Menunggu persetujuan Waka';
                        details.append(status);
                    }
                    if (props.report_url) {
                        const reportLink = document.createElement('a');
                        reportLink.href = props.report_url;
                        reportLink.textContent = 'Lapor';
                        reportLink.className = 'mt-1 inline-flex w-fit rounded-md bg-rose-600 px-2 py-0.5 font-medium text-white text-[10px]';
                        reportLink.dataset.reportEvent = '';
                        reportLink.addEventListener('click', function(event) {
                            event.stopPropagation();
                        });
                        details.append(reportLink);
                    }

                    return { domNodes: [details] };
                },
                dateClick: function(info) {
                    const selectedDate = info.dateStr.slice(0, 10);

                    if (selectedDate < today) {
                        return;
                    }

                    const holiday = calendar.getEvents().find(function (event) {
                        return event.extendedProps.type === 'national_holiday'
                            && event.startStr.slice(0, 10) === selectedDate;
                    });

                    if (holiday && !canBookHoliday) {
                        alert('Organisasi tidak dapat mengajukan kegiatan pada hari libur nasional.');
                        return;
                    }

                    @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isGuru()))
                        openEventModal('create', {
                            date: selectedDate,
                            room_id: '{{ $defaultRoomId }}'
                        });
                    @endif
                },
                eventClick: function(info) {
                    if (info.event.extendedProps.type === 'national_holiday') {
                        return;
                    }

                    if (info.jsEvent.target.closest('[data-report-event]')) {
                        return;
                    }

                    const eventDate = info.event.startStr.slice(0, 10);
                    const dayEvents = calendar.getEvents().filter(function (event) {
                        return event.startStr.slice(0, 10) === eventDate && event.extendedProps.type !== 'national_holiday';
                    });

                    showDayAgenda(eventDate, dayEvents);
                }
            });

            calendarSearchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                calendar.refetchEvents();
            });

            function openEventModal(mode, payload) {
                const statusField = eventForm.querySelector('[name="status"]');
                const statusFieldContainer = statusField?.closest('[data-status-field]');

                eventForm.date.min = today;

                if (mode === 'create') {
                    eventForm.action = '{{ route('events.store') }}';
                    eventForm.querySelector('input[name="_method"]').value = 'POST';
                    modalTitle.textContent = '{{ $isAdmin ? 'Tambah kegiatan' : 'Ajukan kegiatan' }}';
                    eventForm.reset();
                    eventForm.event_name.value = '';
                    eventForm.date.value = payload.date || '';
                    eventForm.room_id.value = payload.room_id || eventForm.room_id.value;
                    statusFieldContainer?.classList.add('hidden');
                    if (statusField) {
                        statusField.value = 'disetujui';
                    }
                    if (sessionSelect) sessionSelect.value = '07:00-08:30';
                    eventForm.start_time.value = '07:00';
                    eventForm.end_time.value = '08:30';
                    checkH3Warning();
                } else {
                    eventForm.action = '/events/' + payload.id;
                    eventForm.querySelector('input[name="_method"]').value = 'PUT';
                    modalTitle.textContent = 'Edit jadwal';
                    statusFieldContainer?.classList.toggle('hidden', !payload.extendedProps.status);
                    eventForm.event_name.value = payload.title || '';
                    eventForm.date.value = payload.start ? payload.start.toISOString().slice(0, 10) : '';
                    eventForm.start_time.value = payload.start ? payload.start.toTimeString().slice(0, 5) : '';
                    eventForm.end_time.value = payload.end ? payload.end.toTimeString().slice(0, 5) : (payload.start ? payload.start.toTimeString().slice(0, 5) : '');
                    eventForm.room_id.value = payload.extendedProps.room_id || eventForm.room_id.value;
                    if (statusField) {
                        statusField.value = payload.extendedProps.status || 'disetujui';
                    }
                    if (sessionSelect) {
                        const timeRange = (eventForm.start_time.value) + '-' + (eventForm.end_time.value);
                        sessionSelect.value = sessionSelect.querySelector(`option[value="${timeRange}"]`) ? timeRange : 'custom';
                    }
                    checkH3Warning();
                }

                eventModal.classList.remove('hidden');
                eventModal.classList.add('flex');
            }

            // Sesi Sekolah & Logika Aturan H-3 Jam
            const sessionSelect = document.getElementById('session_select');
            const startTimeInput = document.getElementById('start_time_input');
            const endTimeInput = document.getElementById('end_time_input');
            const eventDateInput = document.getElementById('event_date_input');
            const h3WarningBox = document.getElementById('h3_warning_box');

            if (sessionSelect) {
                sessionSelect.addEventListener('change', function () {
                    const selected = this.options[this.selectedIndex];
                    const start = selected.getAttribute('data-start');
                    const end = selected.getAttribute('data-end');

                    if (start && end) {
                        startTimeInput.value = start;
                        endTimeInput.value = end;
                    }
                    checkH3Warning();
                });
            }

            function checkH3Warning() {
                if (!eventDateInput || !startTimeInput || !h3WarningBox) {
                    return;
                }

                const selectedDate = eventDateInput.value;
                const startTime = startTimeInput.value;

                if (selectedDate === today && startTime) {
                    const now = new Date();
                    const [hours, minutes] = startTime.split(':').map(Number);
                    const eventStart = new Date();
                    eventStart.setHours(hours, minutes, 0, 0);

                    const diffHours = (eventStart - now) / (1000 * 60 * 60);

                    if (diffHours < 3) {
                        h3WarningBox.classList.remove('hidden');
                    } else {
                        h3WarningBox.classList.add('hidden');
                    }
                } else {
                    h3WarningBox.classList.add('hidden');
                }
            }

            eventDateInput?.addEventListener('change', checkH3Warning);
            startTimeInput?.addEventListener('input', checkH3Warning);

            addEventButton?.addEventListener('click', function () {
                openEventModal('create', {
                    date: today,
                    room_id: '{{ $defaultRoomId }}'
                });
            });

            document.querySelector('[data-close-modal]')?.addEventListener('click', function () {
                eventModal.classList.add('hidden');
            });

            eventModal?.addEventListener('click', function (event) {
                if (event.target === eventModal) {
                    eventModal.classList.add('hidden');
                }
            });

            function renderDateMarkers() {
                if (!calendarEl.isConnected) {
                    return;
                }

                const eventsByDate = new Map();
                const holidaysByDate = new Map();

                calendar.getEvents().forEach(function (event) {
                    const eventDate = event.startStr.slice(0, 10);

                    if (event.extendedProps.type === 'national_holiday') {
                        holidaysByDate.set(eventDate, event);
                        return;
                    }

                    if (!eventsByDate.has(eventDate)) {
                        eventsByDate.set(eventDate, []);
                    }
                    eventsByDate.get(eventDate).push(event);
                });

                calendarEl.querySelectorAll('.fc-daygrid-day[data-date]').forEach(function (dayCell) {
                    const date = dayCell.dataset.date;
                    const holiday = holidaysByDate.get(date);
                    const dayHeader = dayCell.querySelector('.fc-daygrid-day-top');
                    const dayFrame = dayCell.querySelector('.fc-daygrid-day-frame');
                    const isWeekend = [0, 6].includes(new Date(date + 'T12:00:00').getDay());

                    dayCell.classList.toggle('national-holiday-cell', Boolean(holiday));
                    dayCell.classList.toggle('weekend-cell', isWeekend);
                    dayCell.title = [
                        isWeekend ? 'Akhir pekan' : null,
                        holiday ? 'Libur nasional: ' + holiday.title : null
                    ].filter(Boolean).join(' • ');
                    dayCell.querySelectorAll('.day-agenda-button').forEach(btn => btn.remove());
                    dayCell.querySelectorAll('.calendar-day-marker').forEach(marker => marker.remove());

                    if (dayFrame && (isWeekend || holiday)) {
                        const marker = document.createElement('span');
                        marker.className = 'calendar-day-marker';
                        marker.textContent = holiday
                            ? 'Libur nasional: ' + holiday.title
                            : 'Akhir pekan';
                        marker.title = marker.textContent;
                        dayFrame.append(marker);
                    }

                    if (!dayHeader || !eventsByDate.has(date)) {
                        return;
                    }

                    const dayEvents = eventsByDate.get(date);

                    if (dayEvents.length > 1) {
                        const countBadge = document.createElement('button');
                        countBadge.type = 'button';
                        countBadge.className = 'day-agenda-button inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200 transition';
                        countBadge.innerHTML = '⚡ +' + (dayEvents.length - 1) + ' lainnya';
                        countBadge.title = 'Terdapat ' + dayEvents.length + ' kegiatan hari ini. Klik untuk melihat semua agenda.';
                        countBadge.addEventListener('click', function (event) {
                            event.stopPropagation();
                            showDayAgenda(date, dayEvents);
                        });
                        dayHeader.append(countBadge);
                    } else {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'day-agenda-button text-sky-700 hover:bg-sky-100 p-1 rounded-md transition';
                        button.setAttribute('aria-label', 'Lihat seluruh agenda tanggal ' + formatDate(date));
                        button.title = 'Lihat seluruh agenda hari ini';
                        button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" class="h-4 w-4"><circle cx="10" cy="10" r="7.25"></circle><path d="M10 6v4l2.5 1.5"></path></svg>';
                        button.addEventListener('click', function (event) {
                            event.stopPropagation();
                            showDayAgenda(date, dayEvents);
                        });
                        dayHeader.append(button);
                    }
                });
            }

            function showDayAgenda(date, dayEvents) {
                agendaModalTitle.textContent = 'Daftar Agenda Hari ' + formatDate(date);
                agendaList.replaceChildren();

                if (!dayEvents || dayEvents.length === 0) {
                    agendaList.innerHTML = '<p class="text-sm text-slate-500 py-6 text-center">Tidak ada agenda kegiatan pada hari ini.</p>';
                } else {
                    dayEvents
                        .sort(function (first, second) {
                            return (first.extendedProps.start_time || '').localeCompare(second.extendedProps.start_time || '');
                        })
                        .forEach(function (event) {
                            const props = event.extendedProps;
                            const item = document.createElement('article');
                            const topDiv = document.createElement('div');
                            const title = document.createElement('h4');
                            const badge = document.createElement('span');
                            const details = document.createElement('p');
                            const roomLocation = [props.room_name, props.room_location].filter(Boolean).join(', ');
                            const status = props.status === 'diajukan'
                                ? 'Menunggu persetujuan Waka'
                                : 'Disetujui';

                            item.className = 'rounded-xl border border-slate-200 p-3.5 bg-white shadow-xs hover:border-slate-300 transition space-y-2';
                            topDiv.className = 'flex items-center justify-between gap-2';

                            title.className = 'font-bold text-slate-900 text-sm leading-snug';
                            title.textContent = event.title;

                            let badgeClass = 'bg-sky-100 text-sky-800 border-sky-300';
                            if (props.booker_type === 'waka') badgeClass = 'bg-indigo-100 text-indigo-800 border-indigo-300';
                            if (props.booker_type === 'siswa') badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';

                            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap ' + badgeClass;
                            badge.textContent = props.booker_role || 'Guru';
                            topDiv.append(title, badge);

                            details.className = 'whitespace-pre-line text-xs text-slate-600 leading-relaxed';
                            details.textContent = [
                                '⏰ Waktu: ' + (props.start_time || '').slice(0, 5) + ' - ' + (props.end_time || '').slice(0, 5) + ' WIB',
                                '📍 Ruang: ' + (roomLocation || 'Belum ditentukan'),
                                ...(props.penanggung_jawab ? ['👤 Pemohon: ' + props.penanggung_jawab] : []),
                                '📋 Status: ' + status
                            ].join('\n');

                            item.append(topDiv, details);

                            const actionRow = document.createElement('div');
                            actionRow.className = 'flex items-center gap-2 pt-1 border-t border-slate-100';

                            @if (auth()->check() && auth()->user()->isAdmin())
                                const editBtn = document.createElement('button');
                                editBtn.type = 'button';
                                editBtn.className = 'inline-flex items-center gap-1 rounded-lg bg-sky-50 border border-sky-200 px-2.5 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100 transition';
                                editBtn.textContent = '✏ Edit Jadwal';
                                editBtn.addEventListener('click', function () {
                                    agendaModal.classList.add('hidden');
                                    agendaModal.classList.remove('flex');
                                    openEventModal('edit', event);
                                });
                                actionRow.append(editBtn);
                            @endif

                            if (props.report_url) {
                                const reportLink = document.createElement('a');
                                reportLink.href = props.report_url;
                                reportLink.textContent = '📢 Lapor Fasilitas';
                                reportLink.className = 'inline-flex items-center gap-1 rounded-lg bg-rose-50 border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition';
                                actionRow.append(reportLink);
                            }

                            if (props.can_cancel && props.cancel_url) {
                                const cancelForm = document.createElement('form');
                                cancelForm.method = 'POST';
                                cancelForm.action = props.cancel_url;
                                cancelForm.className = 'inline';
                                cancelForm.onsubmit = function () {
                                    return confirm('Apakah Anda yakin ingin membatalkan pengajuan kegiatan "' + event.title + '"?');
                                };

                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = '{{ csrf_token() }}';

                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'DELETE';

                                const cancelBtn = document.createElement('button');
                                cancelBtn.type = 'submit';
                                cancelBtn.className = 'inline-flex items-center gap-1 rounded-lg bg-rose-50 border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition';
                                cancelBtn.textContent = '✕ Batalkan Pengajuan';

                                cancelForm.append(csrfInput, methodInput, cancelBtn);
                                actionRow.append(cancelForm);
                            }

                            if (actionRow.children.length > 0) {
                                item.append(actionRow);
                            }

                            agendaList.append(item);
                        });
                }

                agendaModal.classList.remove('hidden');
                agendaModal.classList.add('flex');
                agendaModal.setAttribute('aria-hidden', 'false');
            }

            function formatDate(date) {
                return new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(new Date(date + 'T12:00:00'));
            }

            document.querySelector('[data-close-agenda]')?.addEventListener('click', function () {
                agendaModal.classList.add('hidden');
                agendaModal.classList.remove('flex');
                agendaModal.setAttribute('aria-hidden', 'true');
            });

            agendaModal.addEventListener('click', function (event) {
                if (event.target === agendaModal) {
                    agendaModal.classList.add('hidden');
                    agendaModal.classList.remove('flex');
                    agendaModal.setAttribute('aria-hidden', 'true');
                }
            });

            calendar.render();
        });
    </script>
@endsection
