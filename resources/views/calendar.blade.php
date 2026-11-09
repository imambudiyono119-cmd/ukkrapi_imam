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
    </style>
    <div class="bg-white rounded-2xl p-4 shadow-sm">
        <form id="calendar-search-form" class="mb-4 flex flex-wrap items-end gap-2">
            <div class="min-w-60 flex-1">
                <label for="calendar-search" class="mb-1 block text-sm font-medium text-slate-700">Cari kegiatan, ruang, atau pemohon</label>
                <input
                    id="calendar-search"
                    type="search"
                    maxlength="100"
                    class="w-full rounded-xl border px-3 py-2"
                    placeholder="Masukkan kata kunci"
                >
            </div>
            <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 font-medium text-white">Cari</button>
        </form>
        @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isGuru()))
            <div class="flex justify-end mb-4">
                <button type="button" id="addEventButton" class="bg-sky-600 text-white px-4 py-2 rounded-xl font-medium">
                    {{ auth()->user()->isAdmin() ? '+ Tambah Kegiatan' : '+ Ajukan Kegiatan' }}
                </button>
            </div>
        @endif

        <div id="calendar"></div>
    </div>

    @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isGuru()))
        <div id="eventModal" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="eventModalTitle" class="font-bold text-lg">Tambah kegiatan</h3>
                    <button type="button" data-close-modal class="text-slate-500">✕</button>
                </div>
                <form id="eventForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm mb-1">Nama agenda</label>
                            <input name="event_name" class="w-full border rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Ruang</label>
                            <select name="room_id" class="w-full border rounded-xl px-3 py-2">
                                @foreach ($roomOptions as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Tanggal</label>
                                <input type="date" name="date" min="{{ $today }}" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            @if (auth()->user()->isAdmin())
                                <div data-status-field>
                                    <label class="block text-sm mb-1">Status persetujuan</label>
                                    <select name="status" class="w-full border rounded-xl px-3 py-2">
                                        <option value="disetujui">Disetujui</option>
                                        <option value="diajukan">Menunggu persetujuan</option>
                                    </select>
                                </div>
                            @else
                                <p class="col-span-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    Agenda akan menunggu persetujuan Waka sebelum disetujui.
                                </p>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Mulai</label>
                                <input type="time" name="start_time" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Selesai</label>
                                <input type="time" name="end_time" class="w-full border rounded-xl px-3 py-2">
                            </div>
                        </div>
                        <button type="submit" id="eventSubmitButton" class="w-full bg-sky-600 text-white py-3 rounded-xl">
                            {{ auth()->user()->isAdmin() ? 'Simpan perubahan' : 'Ajukan kegiatan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div id="agendaModal" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center z-50 p-4" aria-hidden="true">
        <section class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="agendaModalTitle">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 id="agendaModalTitle" class="text-lg font-bold">Agenda hari ini</h3>
                <button type="button" data-close-agenda class="text-slate-500" aria-label="Tutup agenda">✕</button>
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

                    info.el.style.setProperty('--fc-event-bg-color', 'transparent');
                    info.el.style.setProperty('--fc-event-border-color', 'transparent');
                    info.el.style.setProperty('--fc-event-text-color', textColor);
                },
                eventContent: function(info) {
                    const details = document.createElement('div');
                    const title = document.createElement('strong');
                    const time = document.createElement('span');
                    const place = document.createElement('span');
                    const status = document.createElement('span');
                    const props = info.event.extendedProps;
                    const roomLocation = [props.room_name, props.room_location].filter(Boolean).join(', ');

                    if (props.type === 'national_holiday') {
                        return { domNodes: [] };
                    }

                    details.className = 'flex flex-col gap-0.5 overflow-hidden text-[11px] leading-tight';
                    title.className = 'font-semibold';
                    title.textContent = info.event.title;
                    time.textContent = 'Pukul ' + (props.start_time || '').slice(0, 5) + ' - ' + (props.end_time || '').slice(0, 5);
                    place.textContent = 'Tempat: ' + (roomLocation || 'Belum ditentukan');
                    details.append(title, time, place);
                    if (props.status === 'diajukan') {
                        status.textContent = 'Status: Menunggu persetujuan Waka';
                        details.append(status);
                    }
                    if (props.report_url) {
                        const reportLink = document.createElement('a');
                        reportLink.href = props.report_url;
                        reportLink.textContent = 'Lapor';
                        reportLink.className = 'mt-1 inline-flex w-fit rounded-md bg-rose-600 px-2 py-1 font-medium text-white';
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

                    @if (auth()->check() && auth()->user()->isAdmin())
                        openEventModal('edit', info.event);
                    @else
                        const props = info.event.extendedProps;
                        const roomLocation = [props.room_name, props.room_location].filter(Boolean).join(', ');
                        const eventDate = new Intl.DateTimeFormat('id-ID', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        }).format(info.event.start);

                        alert([
                            'Agenda: ' + info.event.title,
                            'Tanggal: ' + eventDate,
                            'Waktu: ' + (props.start_time || '').slice(0, 5) + ' - ' + (props.end_time || '').slice(0, 5),
                            'Tempat: ' + (roomLocation || 'Belum ditentukan'),
                            ...(props.penanggung_jawab ? ['Penanggung jawab: ' + props.penanggung_jawab] : []),
                            ...(props.status ? ['Status: ' + (props.status === 'diajukan' ? 'Menunggu persetujuan Waka' : 'Disetujui')] : [])
                        ].join('\n'));
                    @endif
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
                    eventForm.start_time.value = '08:00';
                    eventForm.end_time.value = '09:00';
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
                }

                eventModal.classList.remove('hidden');
                eventModal.classList.add('flex');
            }

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
                    dayCell.querySelector('.day-agenda-button')?.remove();
                    dayCell.querySelectorAll('.calendar-day-marker').forEach(function (marker) {
                        marker.remove();
                    });

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

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'day-agenda-button';
                    button.setAttribute('aria-label', 'Lihat seluruh agenda tanggal ' + formatDate(date));
                    button.title = 'Lihat seluruh agenda hari ini';
                    button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" class="h-4 w-4"><circle cx="10" cy="10" r="7.25"></circle><path d="M10 6v4l2.5 1.5"></path></svg>';
                    button.addEventListener('click', function (event) {
                        event.stopPropagation();
                        showDayAgenda(date, eventsByDate.get(date));
                    });
                    dayHeader.append(button);
                });
            }

            function showDayAgenda(date, dayEvents) {
                agendaModalTitle.textContent = 'Agenda ' + formatDate(date);
                agendaList.replaceChildren();

                dayEvents
                    .sort(function (first, second) {
                        return first.startStr.localeCompare(second.startStr);
                    })
                    .forEach(function (event) {
                        const props = event.extendedProps;
                        const item = document.createElement('article');
                        const title = document.createElement('h4');
                        const details = document.createElement('p');
                        const roomLocation = [props.room_name, props.room_location].filter(Boolean).join(', ');
                        const status = props.status === 'diajukan'
                            ? 'Menunggu persetujuan Waka'
                            : 'Disetujui';

                        item.className = 'rounded-xl border border-slate-200 p-3';
                        title.className = 'font-semibold text-slate-900';
                        title.textContent = event.title;
                        details.className = 'mt-1 whitespace-pre-line text-sm text-slate-600';
                        details.textContent = [
                            'Pukul ' + (props.start_time || '').slice(0, 5) + ' - ' + (props.end_time || '').slice(0, 5),
                            'Tempat: ' + (roomLocation || 'Belum ditentukan'),
                            ...(props.penanggung_jawab ? ['Penanggung jawab: ' + props.penanggung_jawab] : []),
                            'Status: ' + status
                        ].join('\n');
                        item.append(title, details);
                        agendaList.append(item);
                    });

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
