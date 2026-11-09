<?php

use App\Helpers\WhatsAppLink;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Services\EventBookingService;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->roleWaka = Role::create(['role_name' => 'Waka/Admin']);
    $this->rolePic = Role::create(['role_name' => 'PIC Ruang']);
    $this->roleGuru = Role::create(['role_name' => 'Guru/Organisasi']);

    $this->waka = User::create([
        'username' => 'waka1',
        'password' => bcrypt('password123'),
        'fullname' => 'Waka',
        'phone_number' => '6281234567890',
        'role_id' => $this->roleWaka->id,
    ]);

    $this->pic = User::create([
        'username' => 'pic1',
        'password' => bcrypt('password123'),
        'fullname' => 'PIC 1',
        'phone_number' => '6285550001111',
        'role_id' => $this->rolePic->id,
    ]);

    $this->guru = User::create([
        'username' => 'guru1',
        'password' => bcrypt('password123'),
        'fullname' => 'Guru 1',
        'phone_number' => '6287770002222',
        'role_id' => $this->roleGuru->id,
    ]);

    $this->room = Room::create([
        'room_name' => 'Ruang 101',
        'capacity' => 40,
        'location' => 'Lantai 1',
        'pic_id' => $this->pic->id,
        'is_active' => true,
    ]);
});

test('calendar api includes readable room and time details', function () {
    Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => '8A',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'status' => 'disetujui',
    ]);

    $response = $this->getJson(route('api.events'));

    $response
        ->assertOk()
        ->assertJsonPaths([
            '0.title' => '8A',
            '0.room_name' => 'Ruang 101',
            '0.room_location' => 'Lantai 1',
            '0.start_time' => '08:00:00',
            '0.end_time' => '09:30:00',
        ]);
});

test('calendar booking report link opens a complaint connected to that booking', function () {
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat OSIS',
        'date' => '2026-11-05',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'disetujui',
    ]);

    $this->actingAs($this->guru)
        ->getJson(route('api.events'))
        ->assertOk()
        ->assertJsonPath('0.report_url', route('complaints.index', ['event_id' => $event->id]));

    $this->get(route('complaints.index', ['event_id' => $event->id]))
        ->assertOk()
        ->assertSeeText('Rapat OSIS')
        ->assertSeeText('pada 05-11-2026.')
        ->assertSee('name="event_id" value="'.$event->id.'"', false)
        ->assertSee('name="room_id" value="'.$this->room->id.'"', false);

    $this->post(route('complaints.store'), [
        'event_id' => $event->id,
        'room_id' => $this->room->id,
        'description' => 'Proyektor ruang tidak berfungsi.',
    ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Pengaduan berhasil dikirim.');

    $this->assertDatabaseHas('complaints', [
        'event_id' => $event->id,
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Proyektor ruang tidak berfungsi.',
    ]);
});

test('complaint cannot link a booking to a different room', function () {
    $otherRoom = Room::create([
        'room_name' => 'Ruang 102',
        'capacity' => 30,
        'location' => 'Lantai 1',
        'pic_id' => $this->pic->id,
        'is_active' => true,
    ]);
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat OSIS',
        'date' => '2026-11-05',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'disetujui',
    ]);

    $this->actingAs($this->guru)
        ->post(route('complaints.store'), [
            'event_id' => $event->id,
            'room_id' => $otherRoom->id,
            'description' => 'Kondisi ruang perlu diperiksa.',
        ])
        ->assertSessionHasErrors('event_id');

    $this->assertDatabaseMissing('complaints', [
        'event_id' => $event->id,
        'room_id' => $otherRoom->id,
    ]);
});

test('calendar api includes pending and approved events but hides rejected events', function () {
    foreach ([
        ['Pengajuan menunggu', 'diajukan'],
        ['Jadwal disetujui', 'disetujui'],
        ['Pengajuan ditolak', 'ditolak'],
    ] as [$eventName, $status]) {
        Event::create([
            'room_id' => $this->room->id,
            'pic_id' => $this->guru->id,
            'event_name' => $eventName,
            'date' => '2026-11-10',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => $status,
        ]);
    }

    $this->getJson(route('api.events'))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['title' => 'Pengajuan menunggu', 'status' => 'diajukan'])
        ->assertJsonFragment(['title' => 'Jadwal disetujui', 'status' => 'disetujui'])
        ->assertJsonMissing(['title' => 'Pengajuan ditolak'])
        ->assertJsonMissingPath('0.report_url');
});

test('daily and monthly booking reports show only approved events in the selected period', function () {
    foreach ([
        ['Rapat Harian', '2026-11-05', 'disetujui'],
        ['Rapat Akhir Bulan', '2026-11-30', 'disetujui'],
        ['Rapat Bulan Depan', '2026-12-01', 'disetujui'],
        ['Pengajuan Belum Disetujui', '2026-11-05', 'diajukan'],
    ] as [$eventName, $date, $status]) {
        Event::create([
            'room_id' => $this->room->id,
            'pic_id' => $this->guru->id,
            'event_name' => $eventName,
            'date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => $status,
        ]);
    }

    $this->actingAs($this->waka)
        ->get(route('reports.daily', ['date' => '2026-11-05']))
        ->assertOk()
        ->assertSeeText('Laporan Peminjaman Harian')
        ->assertSeeText('Rapat Harian')
        ->assertSeeText('05-11-2026')
        ->assertDontSeeText('Rapat Akhir Bulan')
        ->assertDontSeeText('Pengajuan Belum Disetujui');

    $this->get(route('reports.monthly', ['month' => '2026-11']))
        ->assertOk()
        ->assertSeeText('Laporan Peminjaman Bulanan')
        ->assertSeeText('Rapat Harian')
        ->assertSeeText('Rapat Akhir Bulan')
        ->assertSeeText('30-11-2026')
        ->assertDontSeeText('Rapat Bulan Depan')
        ->assertDontSeeText('Pengajuan Belum Disetujui');
});

test('calendar search matches event name room and requester', function () {
    $otherRoom = Room::create([
        'room_name' => 'Ruang 202',
        'capacity' => 30,
        'location' => 'Lantai 2',
        'pic_id' => $this->pic->id,
        'is_active' => true,
    ]);

    foreach ([
        ['Agenda khusus', $this->room->id],
        ['Agenda umum', $otherRoom->id],
    ] as [$eventName, $roomId]) {
        Event::create([
            'room_id' => $roomId,
            'pic_id' => $this->guru->id,
            'event_name' => $eventName,
            'date' => '2026-11-10',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => 'disetujui',
        ]);
    }

    $this->getJson(route('api.events', ['search' => 'khusus']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Agenda khusus']);

    $this->getJson(route('api.events', ['search' => 'Ruang 202']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Agenda umum']);

    $this->getJson(route('api.events', ['search' => 'Guru 1']))
        ->assertOk()
        ->assertJsonCount(2);

    $this->getJson(route('api.events', ['search' => str_repeat('a', 101)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('search');
});

test('monthly report search filters bookings and its csv download uses the same filter', function () {
    foreach ([
        ['+SUM(A1) Rapat OSIS', '2026-11-05', 'disetujui'],
        ['Rapat Guru', '2026-11-06', 'disetujui'],
        ['Rapat bulan depan', '2026-12-01', 'disetujui'],
    ] as [$eventName, $date, $status]) {
        Event::create([
            'room_id' => $this->room->id,
            'pic_id' => $this->guru->id,
            'event_name' => $eventName,
            'date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => $status,
        ]);
    }

    $this->actingAs($this->waka)
        ->get(route('reports.monthly', ['month' => '2026-11', 'search' => 'OSIS']))
        ->assertOk()
        ->assertSeeText('+SUM(A1) Rapat OSIS')
        ->assertDontSeeText('Rapat Guru');

    $response = $this->get(route('reports.monthly', [
        'month' => '2026-11',
        'search' => 'OSIS',
        'download' => 'csv',
    ]));

    $response
        ->assertOk()
        ->assertStreamed()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename=laporan-peminjaman-2026-11.csv');

    expect($response->streamedContent())
        ->toContain("'+SUM(A1) Rapat OSIS")
        ->toContain('05-11-2026')
        ->not->toContain('2026-11-05')
        ->not->toContain('Rapat Guru')
        ->not->toContain('Rapat bulan depan');
});

test('room seeder creates all requested facilities and remains idempotent', function () {
    User::create([
        'username' => 'pic2',
        'password' => bcrypt('password123'),
        'fullname' => 'PIC 2',
        'phone_number' => '6285550002222',
        'role_id' => $this->rolePic->id,
    ]);

    $seeder = new RoomSeeder;
    $seeder->run();
    $seeder->run();

    expect(Room::where('room_name', 'like', 'Ruang Teori %')->count())->toBe(18)
        ->and(Room::where('room_name', 'like', 'Lab Komputer %')->count())->toBe(19);

    foreach (['Lapangan', 'Masjid', 'Parkiran Depan', 'Parkiran Belakang', 'Perpustakaan'] as $roomName) {
        $this->assertDatabaseHas('rooms', ['room_name' => $roomName]);
    }
});

test('only waka can view booking reports and their sidebar links both periods', function () {
    $this->actingAs($this->guru)
        ->get(route('reports.daily'))
        ->assertForbidden();

    $this->get(route('reports.monthly', ['download' => 'csv']))
        ->assertForbidden();

    $this->actingAs($this->waka)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('reports.daily').'"', false)
        ->assertSee('href="'.route('reports.monthly').'"', false);
});

test('complaint search filters reports by their description', function () {
    Complaint::create([
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Lampu ruang perlu diganti.',
        'status_repair' => Complaint::STATUS_PENDING,
        'reported_at' => now(),
    ]);
    Complaint::create([
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kursi perlu diperbaiki.',
        'status_repair' => Complaint::STATUS_PENDING,
        'reported_at' => now(),
    ]);

    $this->actingAs($this->guru)
        ->get(route('complaints.index', ['search' => 'Lampu']))
        ->assertOk()
        ->assertSeeText('Lampu ruang perlu diganti.')
        ->assertDontSeeText('Kursi perlu diperbaiki.')
        ->assertSee('name="search"', false);
});

test('complaint history filters by status and defaults to unresolved complaints', function () {
    foreach ([
        ['Laporan kaca', Complaint::STATUS_PENDING],
        ['Laporan AC', Complaint::STATUS_PARTS],
        ['Laporan kursi', Complaint::STATUS_DONE],
    ] as [$description, $status]) {
        Complaint::create([
            'room_id' => $this->room->id,
            'reporter_id' => $this->guru->id,
            'description' => $description,
            'status_repair' => $status,
            'reported_at' => now(),
        ]);
    }

    $this->actingAs($this->guru)
        ->get(route('complaints.index'))
        ->assertOk()
        ->assertSeeText('Laporan kaca')
        ->assertDontSeeText('Laporan AC')
        ->assertDontSeeText('Laporan kursi')
        ->assertSee('value="'.Complaint::STATUS_PENDING.'" selected', false);

    $this->get(route('complaints.index', ['status' => Complaint::STATUS_PARTS]))
        ->assertOk()
        ->assertSeeText('Laporan AC')
        ->assertDontSeeText('Laporan kaca')
        ->assertDontSeeText('Laporan kursi');

    $this->get(route('complaints.index', ['status' => Complaint::STATUS_DONE]))
        ->assertOk()
        ->assertSeeText('Laporan kursi')
        ->assertDontSeeText('Laporan kaca')
        ->assertDontSeeText('Laporan AC');

    $this->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Laporan kaca')
        ->assertSeeText('Laporan AC')
        ->assertSeeText('Laporan kursi');

    $this->get(route('complaints.index', ['status' => 'status-tidak-valid']))
        ->assertSessionHasErrors('status');
});

test('guest can view the calendar without an authenticated user', function () {
    $this->get(route('calendar.public'))
        ->assertOk()
        ->assertSee('data-principal-welcome', false)
        ->assertSeeText('Raharjo, S.IP, M.Pd')
        ->assertSeeText('Kepala Sekolah SMKN 1 Bantul')
        ->assertSee('src="'.asset('images/kepala-sekolah.jpeg').'"', false)
        ->assertSee('id="calendar"', false)
        ->assertSee('id="agendaModal"', false)
        ->assertSee('day-agenda-button', false)
        ->assertSee("eventOrder: 'start,-duration,title'", false)
        ->assertSee('dayMaxEvents: 1', false)
        ->assertSee('Libur nasional: ', false)
        ->assertSee('Akhir pekan', false)
        ->assertSee("info.event.extendedProps.status === 'diajukan'", false)
        ->assertSee("textColor = '#a16207'", false)
        ->assertSee("textColor = '#000'", false);
});

test('calendar holiday api returns red national holiday background dates', function () {
    $this->getJson(route('api.holidays'))
        ->assertOk()
        ->assertJsonFragment([
            'title' => 'Hari Raya Natal',
            'start' => '2026-12-25',
            'display' => 'background',
            'backgroundColor' => '#fee2e2',
        ]);
});

test('student sidebar only shows dashboard and calendar', function () {
    $studentRole = Role::create(['role_name' => 'Siswa/Tamu']);
    $student = User::create([
        'username' => 'student1',
        'password' => bcrypt('password123'),
        'fullname' => 'Siswa Satu',
        'role_id' => $studentRole->id,
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard')
        ->assertSeeText('Kalender')
        ->assertDontSee('href="'.route('complaints.index').'"', false);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('href="'.route('dashboard').'"', false)
        ->assertDontSeeText('Dashboard')
        ->assertSee('href="'.route('calendar.public').'"', false);

    $this->actingAs($this->waka)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('complaints.index').'"', false);
});

test('guru dashboard welcomes them and only shows calendar in the sidebar', function () {
    $this->actingAs($this->guru)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-principal-welcome', false)
        ->assertDontSeeText('Raharjo, S.IP, M.Pd')
        ->assertSee('src="'.asset('images/logo-smkn-1-bantul.png').'"', false)
        ->assertDontSeeText('Agenda hari ini')
        ->assertDontSeeText('Pengaduan tertunda')
        ->assertSee('href="'.route('calendar.public').'"', false)
        ->assertSee('href="'.route('complaints.index').'"', false)
        ->assertDontSee('href="'.route('home').'"', false)
        ->assertDontSee('href="'.route('dashboard').'"', false)
        ->assertDontSee('href="'.route('master-data.index').'"', false);
});

test('guru can submit and view an uploaded complaint photo', function () {
    Storage::fake('public');

    $photo = UploadedFile::fake()->image('damage.jpg');

    $this->actingAs($this->guru)
        ->get(route('complaints.index'))
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertDontSee('capture="environment"', false);

    $this->post(route('complaints.store'), [
        'room_id' => $this->room->id,
        'description' => 'Kursi ruang kelas rusak.',
        'photo' => $photo,
    ])->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Pengaduan berhasil dikirim.');

    $complaint = Complaint::where('reporter_id', $this->guru->id)->firstOrFail();

    expect($complaint->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($complaint->photo_path);

    $this->get(route('complaints.index'))
        ->assertOk()
        ->assertSee('src="'.Storage::disk('public')->url($complaint->photo_path).'"', false);
});

test('teacher can submit an event on a national holiday', function () {
    $teacherRole = Role::firstOrCreate(['role_name' => 'Guru']);
    $teacher = User::create([
        'username' => 'teacher-holiday',
        'password' => 'password123',
        'fullname' => 'Guru Hari Libur',
        'role_id' => $teacherRole->id,
    ]);
    $this->travelTo('2026-11-06 09:00:00');

    $this->actingAs($teacher)
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Kegiatan Guru saat Natal',
            'date' => '2026-12-25',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ])
        ->assertRedirect(route('calendar.public'));

    $this->assertDatabaseHas('events', [
        'event_name' => 'Kegiatan Guru saat Natal',
        'pic_id' => $teacher->id,
        'date' => '2026-12-25',
        'status' => 'diajukan',
    ]);
});

test('organization cannot submit an event on a national holiday', function () {
    $organizationRole = Role::firstOrCreate(['role_name' => 'Organisasi']);
    $organization = User::create([
        'username' => 'organization-holiday',
        'password' => 'password123',
        'fullname' => 'Organisasi Hari Libur',
        'role_id' => $organizationRole->id,
    ]);
    $this->travelTo('2026-11-06 09:00:00');

    $this->actingAs($organization)
        ->from(route('calendar.public'))
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Kegiatan Organisasi saat Natal',
            'date' => '2026-12-25',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ])
        ->assertSessionHasErrors([
            'date' => 'Hanya Guru dan Waka yang dapat mengajukan kegiatan pada hari libur nasional.',
        ]);

    $this->assertDatabaseMissing('events', [
        'event_name' => 'Kegiatan Organisasi saat Natal',
        'pic_id' => $organization->id,
    ]);
});

test('calendar does not accept a new event on a date that has passed', function () {
    $teacherRole = Role::firstOrCreate(['role_name' => 'Guru']);
    $teacher = User::create([
        'username' => 'teacher-past-date',
        'password' => 'password123',
        'fullname' => 'Guru Tanggal Lampau',
        'role_id' => $teacherRole->id,
    ]);
    $this->travelTo('2026-11-06 09:00:00');

    $this->actingAs($teacher)
        ->from(route('calendar.public'))
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Kegiatan Lampau',
            'date' => '2026-11-05',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ])
        ->assertSessionHasErrors([
            'date' => 'Tanggal kegiatan tidak boleh sebelum hari ini.',
        ]);

    $this->assertDatabaseMissing('events', ['event_name' => 'Kegiatan Lampau']);
});

test('guru can submit an event that waits for waka approval', function () {
    $this->travelTo('2026-10-01 09:00:00');

    $this->actingAs($this->guru)
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Rapat OSIS',
            'date' => '2026-10-10',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'event_name' => 'Rapat OSIS',
        'pic_id' => $this->guru->id,
        'status' => 'diajukan',
    ]);
});

test('guru calendar shows the submission action and approval notice', function () {
    $this->actingAs($this->guru)
        ->get(route('calendar.public'))
        ->assertOk()
        ->assertSeeText('Ajukan Kegiatan')
        ->assertSeeText('Agenda akan menunggu persetujuan Waka sebelum disetujui.');
});

test('waka-created calendar events are automatically approved without status or pic details', function () {
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->waka->id,
        'event_name' => 'Rapat Waka',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'disetujui',
    ]);

    $response = $this->getJson(route('api.events'));

    $response
        ->assertOk()
        ->assertJsonPath('0.id', $event->id)
        ->assertJsonPath('0.status', 'disetujui')
        ->assertJsonMissingPaths(['0.penanggung_jawab']);
});

test('waka-created events are approved even when the request asks for pending status', function () {
    $this->travelTo('2026-10-01 09:00:00');

    $this->actingAs($this->waka)
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Upacara Sekolah',
            'date' => '2026-10-10',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => 'diajukan',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'event_name' => 'Upacara Sekolah',
        'pic_id' => $this->waka->id,
        'status' => 'disetujui',
    ]);
});

test('pic cannot submit calendar events', function () {
    $this->actingAs($this->pic)
        ->post(route('events.store'), [
            'room_id' => $this->room->id,
            'event_name' => 'Agenda PIC',
            'date' => '2026-10-10',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ])
        ->assertForbidden();
});

test('waka dashboard shows pending schedule notifications and decision options', function () {
    Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat Pengurus OSIS',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->waka)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Notifikasi pengajuan jadwal')
        ->assertSeeText('Rapat Pengurus OSIS')
        ->assertSeeText('10-10-2026')
        ->assertSeeText('Setujui')
        ->assertSeeText('Tolak')
        ->assertSee('name="rejection_reason"', false)
        ->assertSee('required', false)
        ->assertSeeText('Pindahkan');
});

test('pending schedule notifications are hidden from guru users', function () {
    Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat Pengurus OSIS',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->guru)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSeeText('Notifikasi pengajuan jadwal')
        ->assertDontSeeText('Rapat Pengurus OSIS');
});

test('waka can approve or reject pending event submissions', function (string $decision) {
    $rejectionReason = $decision === 'ditolak' ? 'Ruang tidak tersedia pada tanggal tersebut.' : null;
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat Guru',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->waka)
        ->patch(route('events.decision', $event), [
            'status' => $decision,
            'rejection_reason' => $rejectionReason,
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'status' => $decision,
        'approved_by' => $decision === 'disetujui' ? $this->waka->id : null,
        'rejection_reason' => $rejectionReason,
    ]);

    $notification = $this->guru->notifications()->sole();

    expect($notification->data)->toMatchArray([
        'event_id' => $event->id,
        'event_name' => 'Rapat Guru',
        'action' => $decision === 'disetujui' ? 'approved' : 'rejected',
        'status' => $decision,
        'rejection_reason' => $rejectionReason,
    ]);

    $message = $decision === 'disetujui'
        ? 'Jadwal "Rapat Guru" telah disetujui oleh Waka.'
        : 'Pengajuan jadwal "Rapat Guru" ditolak oleh Waka. Alasan: '.$rejectionReason;

    $this->actingAs($this->guru)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText($message);

    expect($notification->fresh()->read_at)->not->toBeNull();
})->with(['approval' => 'disetujui', 'rejection' => 'ditolak']);

test('waka cannot reject a schedule submission without providing a reason', function () {
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat Guru',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->waka)
        ->patch(route('events.decision', $event), ['status' => 'ditolak'])
        ->assertSessionHasErrors('rejection_reason');

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'status' => 'diajukan',
        'rejection_reason' => null,
    ]);
    expect($this->guru->notifications()->exists())->toBeFalse();
});

test('only waka can decide pending event submissions', function () {
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Rapat Guru',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->guru)
        ->patch(route('events.decision', $event), ['status' => 'disetujui'])
        ->assertForbidden();

    $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'diajukan']);
});

test('waka can move events whether pending or approved', function (string $status) {
    $this->travelTo('2026-10-01 09:00:00');

    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda Pindah',
        'date' => '2026-10-10',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => $status,
    ]);
    $destinationRoom = Room::create([
        'room_name' => 'Aula Baru',
        'capacity' => 80,
        'location' => 'Lantai 3',
        'pic_id' => $this->pic->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->waka)
        ->put(route('events.update', $event), [
            'room_id' => $destinationRoom->id,
            'event_name' => $event->event_name,
            'date' => '2026-10-11',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'move_reason' => 'Penyesuaian jadwal sekolah',
        ])
        ->assertRedirect(route('calendar.public'));

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'room_id' => $destinationRoom->id,
        'date' => '2026-10-11',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => $status,
        'move_reason' => 'Penyesuaian jadwal sekolah',
    ]);

    $notification = $this->guru->notifications()->sole();

    expect($notification->data)->toMatchArray([
        'event_id' => $event->id,
        'action' => 'moved',
        'status' => $status,
        'date' => '2026-10-11',
        'room_name' => 'Aula Baru',
    ]);
})->with(['pending' => 'diajukan', 'approved' => 'disetujui']);

test('overlapping room booking is rejected', function () {
    Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda lama',
        'date' => '2026-10-10',
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'status' => 'disetujui',
    ]);

    $service = new EventBookingService;

    expect($service->hasConflict($this->room->id, '2026-10-10', '10:00:00', '11:30:00'))->toBeTrue();

    expect(fn () => $service->create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda baru',
        'date' => '2026-10-10',
        'start_time' => '10:00:00',
        'end_time' => '11:30:00',
        'status' => 'diajukan',
    ]))->toThrow(
        InvalidArgumentException::class,
        'Agenda yang bentrok: Agenda lama (09:00:00 - 11:00:00).'
    );
});

test('touching time slots are allowed', function () {
    Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda lama',
        'date' => '2026-10-10',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'disetujui',
    ]);

    $service = new EventBookingService;
    $this->expectNotToPerformAssertions();
    $service->create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda baru',
        'date' => '2026-10-10',
        'start_time' => '11:00:00',
        'end_time' => '12:00:00',
        'status' => 'diajukan',
    ]);
});

test('move room fills move reason and rejects invalid move', function () {
    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda lama',
        'date' => '2026-10-10',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'status' => 'disetujui',
    ]);

    $service = new EventBookingService;
    $service->moveRoom($event, $this->room->id, 'Alasan pindah');

    expect($event->fresh()->move_reason)->toBe('Alasan pindah');

    $otherRoom = Room::create([
        'room_name' => 'Ruang 102',
        'capacity' => 30,
        'location' => 'Lantai 1',
        'pic_id' => $this->pic->id,
        'is_active' => true,
    ]);

    expect(fn () => $service->moveRoom($event, $otherRoom->id, ''))->toThrow(InvalidArgumentException::class, 'Alasan pindah ruang wajib diisi.');
});

test('complaint status must be valid and whatsapp number is normalized', function () {
    $complaint = Complaint::create([
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'AC rusak',
        'status_repair' => 'Belum Diperbaiki',
        'handler_id' => $this->pic->id,
        'reported_at' => now(),
    ]);

    expect(WhatsAppLink::forComplaint($complaint))->toContain('wa.me/6285550001111');

    expect(fn () => Complaint::create([
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Status invalid',
        'status_repair' => 'Tidak Valid',
        'reported_at' => now(),
    ]))->toThrow(InvalidArgumentException::class, 'Status perbaikan tidak valid.');
});

test('waka can update an event from the calendar and it creates a global notification', function () {
    $this->travelTo('2026-10-01 09:00:00');

    $event = Event::create([
        'room_id' => $this->room->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Agenda lama',
        'date' => '2026-10-10',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->waka);

    $response = $this->put(route('events.update', $event), [
        'room_id' => $this->room->id,
        'event_name' => 'Agenda baru',
        'date' => '2026-10-10',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'disetujui',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('events', ['id' => $event->id, 'event_name' => 'Agenda baru']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'disetujui']);
    expect(Cache::get('global_notifications')[0]['message'])
        ->toBe('Jadwal "Agenda baru" diperbarui pada 10-10-2026 10:00:00 - 11:00:00');
});

test('waka and pic can update complaint status', function () {
    $complaint = Complaint::create([
        'room_id' => $this->room->id,
        'reporter_id' => $this->guru->id,
        'description' => 'AC rusak',
        'status_repair' => 'Belum Diperbaiki',
        'handler_id' => null,
        'reported_at' => now(),
    ]);

    $this->actingAs($this->pic)
        ->patch(route('complaints.updateStatus', $complaint), ['status_repair' => 'Sudah Diperbaiki'])
        ->assertRedirect();

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status_repair' => 'Sudah Diperbaiki']);

    $this->actingAs($this->waka)
        ->patch(route('complaints.updateStatus', $complaint), ['status_repair' => 'Menunggu Spare Part'])
        ->assertRedirect();

    $complaint->refresh();
    expect($complaint->status_repair)->toBe('Menunggu Spare Part');
});
