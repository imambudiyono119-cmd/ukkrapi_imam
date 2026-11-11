<?php

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->roleWaka = Role::firstOrCreate(['role_name' => 'Waka/Admin']);
    $this->rolePic = Role::firstOrCreate(['role_name' => 'PIC Ruang']);
    $this->roleGuru = Role::firstOrCreate(['role_name' => 'Guru']);
    $this->roleOsis = Role::firstOrCreate(['role_name' => 'Organisasi']);

    $this->waka = User::create([
        'username' => 'waka_user',
        'password' => bcrypt('password123'),
        'fullname' => 'Waka Sarpras',
        'email' => 'waka@test.sch.id',
        'phone_number' => '6281234567890',
        'role_id' => $this->roleWaka->id,
    ]);

    $this->pic1 = User::create([
        'username' => 'pic_ruang1',
        'password' => bcrypt('password123'),
        'fullname' => 'PIC Lab Komputer',
        'email' => 'pic1@test.sch.id',
        'phone_number' => '6285550001111',
        'role_id' => $this->rolePic->id,
    ]);

    $this->pic2 = User::create([
        'username' => 'pic_ruang2',
        'password' => bcrypt('password123'),
        'fullname' => 'PIC Ruang Teori',
        'email' => 'pic2@test.sch.id',
        'phone_number' => '6285550002222',
        'role_id' => $this->rolePic->id,
    ]);

    $this->guru = User::create([
        'username' => 'guru_user',
        'password' => bcrypt('password123'),
        'fullname' => 'Budi Guru',
        'email' => 'guru@test.sch.id',
        'phone_number' => '6287770002222',
        'role_id' => $this->roleGuru->id,
    ]);

    $this->osis = User::create([
        'username' => 'osis_user',
        'password' => bcrypt('password123'),
        'fullname' => 'Ketua OSIS',
        'email' => 'osis@test.sch.id',
        'phone_number' => '6288880003333',
        'role_id' => $this->roleOsis->id,
    ]);

    $this->roomLab = Room::create([
        'room_name' => 'Lab Komputer 1',
        'capacity' => 36,
        'location' => 'Gedung B Lantai 2',
        'pic_id' => $this->pic1->id,
        'is_active' => true,
    ]);

    $this->roomTeori = Room::create([
        'room_name' => 'Ruang Teori 10',
        'capacity' => 32,
        'location' => 'Gedung A Lantai 1',
        'pic_id' => $this->pic2->id,
        'is_active' => true,
    ]);
});

test('mobile hamburger navbar is rendered in layout', function () {
    $this->actingAs($this->guru)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="navbar-toggle"', false)
        ->assertSee('id="mobile-nav"', false)
        ->assertSee('id="hamburger-icon"', false);
});

test('reporter can submit complaint with both photo and document file', function () {
    Storage::fake('public');

    $photo = UploadedFile::fake()->image('pc-rusak.jpg');
    $document = UploadedFile::fake()->create('laporan-kerusakan.pdf', 500, 'application/pdf');

    $this->actingAs($this->guru)
        ->post(route('complaints.store'), [
            'room_id' => $this->roomLab->id,
            'description' => 'Monitor PC no 5 mati total dan mouse rusak.',
            'photo' => $photo,
            'file' => $document,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success');

    $complaint = Complaint::where('reporter_id', $this->guru->id)->firstOrFail();

    expect($complaint->photo_path)->not->toBeNull()
        ->and($complaint->file_path)->not->toBeNull();

    Storage::disk('public')->assertExists($complaint->photo_path);
    Storage::disk('public')->assertExists($complaint->file_path);

    $this->get(route('complaints.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($complaint->photo_path), false)
        ->assertSee(Storage::disk('public')->url($complaint->file_path), false)
        ->assertSeeText('Unduh Dokumen Pelapor');
});

test('pic cannot submit complaints and complaint form is hidden for pic', function () {
    $this->actingAs($this->pic1)
        ->get(route('complaints.index'))
        ->assertOk()
        ->assertSeeText('Panel Penanganan PIC Ruang')
        ->assertDontSeeText('Buat Laporan Pengaduan')
        ->assertDontSee('name="description"', false);

    $this->post(route('complaints.store'), [
        'room_id' => $this->roomLab->id,
        'description' => 'Mencoba mengadu sebagai PIC',
    ])
        ->assertSessionHasErrors('error');

    $this->assertDatabaseMissing('complaints', ['description' => 'Mencoba mengadu sebagai PIC']);
});

test('pic can update complaint status and upload handler photo with camera and document file', function () {
    Storage::fake('public');

    $complaint = Complaint::create([
        'room_id' => $this->roomLab->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kabel LAN putus.',
        'status_repair' => Complaint::STATUS_PENDING,
        'reported_at' => now(),
    ]);

    $handlerPhoto = UploadedFile::fake()->image('hasil-perbaikan.jpg');
    $handlerFile = UploadedFile::fake()->create('berita-acara.pdf', 300, 'application/pdf');

    $this->actingAs($this->pic1)
        ->patch(route('complaints.updateStatus', $complaint), [
            'status_repair' => Complaint::STATUS_DONE,
            'handler_note' => 'Kabel LAN baru sudah dipasang dan jaringan lancar.',
            'handler_photo' => $handlerPhoto,
            'handler_file' => $handlerFile,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $complaint->refresh();

    expect($complaint->status_repair)->toBe(Complaint::STATUS_DONE)
        ->and($complaint->handler_id)->toBe($this->pic1->id)
        ->and($complaint->handler_note)->toBe('Kabel LAN baru sudah dipasang dan jaringan lancar.')
        ->and($complaint->handler_photo_path)->not->toBeNull()
        ->and($complaint->handler_file_path)->not->toBeNull();

    Storage::disk('public')->assertExists($complaint->handler_photo_path);
    Storage::disk('public')->assertExists($complaint->handler_file_path);
});

test('complaint history only displays own reports for teacher and osis', function () {
    $complaintGuru = Complaint::create([
        'room_id' => $this->roomLab->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Laporan khusus Guru Budi',
        'status_repair' => Complaint::STATUS_PENDING,
        'reported_at' => now(),
    ]);

    $complaintOsis = Complaint::create([
        'room_id' => $this->roomTeori->id,
        'reporter_id' => $this->osis->id,
        'description' => 'Laporan khusus OSIS Rapat',
        'status_repair' => Complaint::STATUS_PENDING,
        'reported_at' => now(),
    ]);

    // Guru only sees Guru's report, NOT OSIS's
    $this->actingAs($this->guru)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Laporan khusus Guru Budi')
        ->assertDontSeeText('Laporan khusus OSIS Rapat');

    // OSIS only sees OSIS's report, NOT Guru's
    $this->actingAs($this->osis)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Laporan khusus OSIS Rapat')
        ->assertDontSeeText('Laporan khusus Guru Budi');

    // PIC Lab only sees complaints for Lab Komputer
    $this->actingAs($this->pic1)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Laporan khusus Guru Budi')
        ->assertDontSeeText('Laporan khusus OSIS Rapat');

    // Admin sees all complaints
    $this->actingAs($this->waka)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Laporan khusus Guru Budi')
        ->assertSeeText('Laporan khusus OSIS Rapat');
});

test('monthly booking report can be downloaded as formatted excel table and print view', function () {
    Event::create([
        'room_id' => $this->roomLab->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Ujian Sertifikasi Siswa',
        'date' => '2026-11-15',
        'start_time' => '07:30:00',
        'end_time' => '11:30:00',
        'status' => 'disetujui',
    ]);

    $this->actingAs($this->waka)
        ->get(route('reports.monthly', ['month' => '2026-11']))
        ->assertOk()
        ->assertSee('value="excel"', false)
        ->assertSee('value="print"', false)
        ->assertSeeText('Unduh Excel (Tabel Rapi)')
        ->assertSeeText('Cetak / PDF');

    $excelResponse = $this->get(route('reports.monthly', [
        'month' => '2026-11',
        'download' => 'excel',
    ]));

    $excelResponse
        ->assertOk()
        ->assertStreamed()
        ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename=laporan-peminjaman-2026-11.xls');

    expect($excelResponse->streamedContent())
        ->toContain('LAPORAN BULANAN PEMINJAMAN RUANG')
        ->toContain('SMK NEGERI 1 BANTUL')
        ->toContain('Ujian Sertifikasi Siswa')
        ->toContain('Lab Komputer 1')
        ->toContain('TOTAL KEGIATAN DISETUJUI');

    $printResponse = $this->get(route('reports.monthly', [
        'month' => '2026-11',
        'download' => 'print',
    ]));

    $printResponse
        ->assertOk()
        ->assertSeeText('Laporan Bulanan Peminjaman Ruang')
        ->assertSeeText('Ujian Sertifikasi Siswa')
        ->assertSeeText('Cetak / Simpan PDF');
});

test('ruang wijaya kusuma exists in database', function () {
    expect(Room::where('room_name', 'Ruang Wijaya Kusuma')->exists())->toBeTrue();
});

test('csv download button is removed from waka booking reports', function () {
    $this->actingAs($this->waka)
        ->get(route('reports.monthly'))
        ->assertOk()
        ->assertSee('value="excel"', false)
        ->assertSee('value="print"', false)
        ->assertDontSee('value="csv"', false)
        ->assertDontSeeText('Unduh CSV');
});

test('event booking enforces h-3 hours rule for non-admin', function () {
    $this->travelTo('2026-11-10 09:00:00');

    // Trying to book for today at 10:30 (only 1.5 hours ahead) must fail
    $this->actingAs($this->guru)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Rapat Mendadak',
            'date' => '2026-11-10',
            'start_time' => '10:30:00',
            'end_time' => '11:30:00',
        ])
        ->assertSessionHasErrors('start_time');

    // Booking for today at 13:00 (4 hours ahead) succeeds
    $this->actingAs($this->guru)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Pembelajaran Sesi Siang',
            'date' => '2026-11-10',
            'start_time' => '13:00:00',
            'end_time' => '14:30:00',
        ])
        ->assertRedirect(route('calendar.public'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('events', [
        'event_name' => 'Pembelajaran Sesi Siang',
        'pic_id' => $this->guru->id,
    ]);
});

test('calendar api returns role-based highlights for waka, guru, and osis', function () {
    $eventWaka = Event::create([
        'room_id' => $this->roomLab->id,
        'pic_id' => $this->waka->id,
        'event_name' => 'Upacara Hari Pahlawan',
        'date' => '2026-11-10',
        'start_time' => '07:00:00',
        'end_time' => '08:30:00',
        'status' => 'disetujui',
    ]);

    $eventGuru = Event::create([
        'room_id' => $this->roomLab->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Praktikum Komputer Guru',
        'date' => '2026-11-10',
        'start_time' => '08:30:00',
        'end_time' => '10:00:00',
        'status' => 'disetujui',
    ]);

    $eventOsis = Event::create([
        'room_id' => $this->roomTeori->id,
        'pic_id' => $this->osis->id,
        'event_name' => 'Rapat Pengurus OSIS',
        'date' => '2026-11-10',
        'start_time' => '14:00:00',
        'end_time' => '15:30:00',
        'status' => 'diajukan',
    ]);

    $response = $this->getJson(route('api.events'));
    $response->assertOk();

    $wakaJson = collect($response->json())->firstWhere('id', $eventWaka->id);
    expect($wakaJson['booker_type'])->toBe('waka')
        ->and($wakaJson['backgroundColor'])->toBe('#4f46e5');

    $guruJson = collect($response->json())->firstWhere('id', $eventGuru->id);
    expect($guruJson['booker_type'])->toBe('guru')
        ->and($guruJson['backgroundColor'])->toBe('#0284c7');

    $osisJson = collect($response->json())->firstWhere('id', $eventOsis->id);
    expect($osisJson['booker_type'])->toBe('siswa')
        ->and($osisJson['backgroundColor'])->toBe('#059669');
});

test('calendar view includes school session selector and legend highlights', function () {
    $this->actingAs($this->guru)
        ->get(route('calendar.public'))
        ->assertOk()
        ->assertSee('id="session_select"', false)
        ->assertSee('Sesi 1 (07:30 - 08:30)', false)
        ->assertSee('Sesi 2 (08:30 - 10:00)', false)
        ->assertSee('Sesi 3 (10:15 - 11:45)', false)
        ->assertSee('Highlight Peminjam', false)
        ->assertSee('Waka/Admin Langsung', false)
        ->assertSee('H-3 jam', false);
});

test('requester and waka can cancel an event submission but unauthorized user cannot', function () {
    $event = Event::create([
        'room_id' => $this->roomLab->id,
        'pic_id' => $this->guru->id,
        'event_name' => 'Bimbingan Olimpiade Guru',
        'date' => '2026-11-20',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
        'status' => 'diajukan',
    ]);

    // Unauthorized user (OSIS) cannot cancel Guru's event
    $this->actingAs($this->osis)
        ->delete(route('events.destroy', $event))
        ->assertForbidden();

    expect(Event::whereKey($event->id)->exists())->toBeTrue();

    // Requester (Guru) can cancel their own event
    $this->actingAs($this->guru)
        ->delete(route('events.destroy', $event))
        ->assertSessionHas('success');

    expect(Event::whereKey($event->id)->exists())->toBeFalse();

    // Waka can cancel any event
    $event2 = Event::create([
        'room_id' => $this->roomTeori->id,
        'pic_id' => $this->osis->id,
        'event_name' => 'Latihan Debat OSIS',
        'date' => '2026-11-21',
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'status' => 'diajukan',
    ]);

    $this->actingAs($this->waka)
        ->delete(route('events.destroy', $event2))
        ->assertSessionHas('success');

    expect(Event::whereKey($event2->id)->exists())->toBeFalse();
});

test('guru can book facilities at night or for 24 hours while students cannot', function () {
    $futureDate = now()->addDays(5)->toDateString();

    // 1. Guru booking at night (18:30 - 22:00) must succeed
    $this->actingAs($this->guru)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Koreksi Ujian Guru Malam Hari',
            'date' => $futureDate,
            'start_time' => '18:30',
            'end_time' => '22:00',
        ])
        ->assertRedirect(route('calendar.public'))
        ->assertSessionHas('success');

    // 2. Guru booking 24 hours (00:00 - 23:59) on another room must succeed
    $this->actingAs($this->guru)
        ->post(route('events.store'), [
            'room_id' => $this->roomTeori->id,
            'event_name' => 'Pameran Karya Guru 24 Jam',
            'date' => $futureDate,
            'start_time' => '00:00',
            'end_time' => '23:59',
        ])
        ->assertRedirect(route('calendar.public'))
        ->assertSessionHas('success');

    // 3. Siswa/Organisasi booking at night must fail with validation error
    $otherDate = now()->addDays(6)->toDateString();
    $this->actingAs($this->osis)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Rapat Malam OSIS',
            'date' => $otherDate,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ])
        ->assertSessionHasErrors(['start_time']);

    // 4. Siswa/Organisasi booking 24 hours must fail
    $this->actingAs($this->osis)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Acara OSIS 24 Jam',
            'date' => $otherDate,
            'start_time' => '00:00',
            'end_time' => '23:59',
        ])
        ->assertSessionHasErrors(['start_time']);

    // 5. Siswa/Organisasi booking within school hours (07:30 - 15:30) must succeed
    $this->actingAs($this->osis)
        ->post(route('events.store'), [
            'room_id' => $this->roomLab->id,
            'event_name' => 'Kegiatan OSIS Siang',
            'date' => $otherDate,
            'start_time' => '07:30',
            'end_time' => '15:30',
        ])
        ->assertRedirect(route('calendar.public'))
        ->assertSessionHas('success');
});

test('pic assignments: pic1 for classrooms, udin frimawan pic3 for all labs, and pic2 for general facilities', function () {
    $pic1 = User::firstOrCreate(
        ['username' => 'pic1'],
        [
            'fullname' => 'PIC 1 (Ruang Teori & Kelas)',
            'phone_number' => '6285550001111',
            'password' => bcrypt('password123'),
            'role_id' => $this->rolePic->id,
        ]
    );
    $pic2 = User::firstOrCreate(
        ['username' => 'pic2'],
        [
            'fullname' => 'PIC 2 (Fasilitas Selain Lab & Kelas)',
            'phone_number' => '6285550002222',
            'password' => bcrypt('password123'),
            'role_id' => $this->rolePic->id,
        ]
    );
    $pic3 = User::firstOrCreate(
        ['username' => 'pic3'],
        [
            'fullname' => 'Udin Frimawan',
            'phone_number' => '6285550003333',
            'password' => bcrypt('password123'),
            'role_id' => $this->rolePic->id,
        ]
    );

    expect($pic3)->not->toBeNull()
        ->and($pic3->fullname)->toBe('Udin Frimawan');

    // Create rooms matching the categories
    $labKomputer = Room::create([
        'room_name' => 'Lab Komputer Baru',
        'capacity' => 36,
        'location' => 'Lantai 2',
        'pic_id' => $pic3->id,
        'is_active' => true,
    ]);

    $ruangTeori = Room::create([
        'room_name' => 'Ruang Teori 99',
        'capacity' => 36,
        'location' => 'Lantai 1',
        'pic_id' => $pic1->id,
        'is_active' => true,
    ]);

    $ruangWijaya = Room::create([
        'room_name' => 'Aula Wijaya Hall',
        'capacity' => 100,
        'location' => 'Lantai 3',
        'pic_id' => $pic2->id,
        'is_active' => true,
    ]);

    // Create complaints for each room
    $complaintLab = Complaint::create([
        'room_id' => $labKomputer->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kerusakan PC di Lab Komputer',
        'status_repair' => 'Belum Diperbaiki',
        'reported_at' => now(),
    ]);

    $complaintTeori = Complaint::create([
        'room_id' => $ruangTeori->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kerusakan Papan Tulis Ruang Teori',
        'status_repair' => 'Belum Diperbaiki',
        'reported_at' => now(),
    ]);

    $complaintUmum = Complaint::create([
        'room_id' => $ruangWijaya->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kerusakan Sound System Aula',
        'status_repair' => 'Belum Diperbaiki',
        'reported_at' => now(),
    ]);

    // Udin Frimawan (pic3) only sees lab complaints
    $this->actingAs($pic3)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Kerusakan PC di Lab Komputer')
        ->assertDontSeeText('Kerusakan Papan Tulis Ruang Teori')
        ->assertDontSeeText('Kerusakan Sound System Aula');

    // PIC 1 only sees classroom/teori complaints
    $this->actingAs($pic1)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Kerusakan Papan Tulis Ruang Teori')
        ->assertDontSeeText('Kerusakan PC di Lab Komputer')
        ->assertDontSeeText('Kerusakan Sound System Aula');

    // PIC 2 only sees non-lab & non-classroom complaints
    $this->actingAs($pic2)
        ->get(route('complaints.index', ['status' => 'all']))
        ->assertOk()
        ->assertSeeText('Kerusakan Sound System Aula')
        ->assertDontSeeText('Kerusakan PC di Lab Komputer')
        ->assertDontSeeText('Kerusakan Papan Tulis Ruang Teori');
});

test('waka dashboard shows warning for complaints unhandled for 3 days or more with whatsapp link', function () {
    $picUdin = User::firstOrCreate(
        ['username' => 'pic3'],
        [
            'fullname' => 'Udin Frimawan',
            'phone_number' => '6285550003333',
            'password' => bcrypt('password123'),
            'role_id' => $this->rolePic->id,
        ]
    );

    $lab = Room::create([
        'room_name' => 'Lab Multimedia 5',
        'capacity' => 30,
        'location' => 'Lantai 2',
        'pic_id' => $picUdin->id,
        'is_active' => true,
    ]);

    // Complaint reported 4 days ago and still pending
    $overdueComplaint = Complaint::create([
        'room_id' => $lab->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Kabel LAN putus belum diganti',
        'status_repair' => 'Belum Diperbaiki',
        'reported_at' => now()->subDays(4),
    ]);

    // Complaint reported today (not overdue)
    $recentComplaint = Complaint::create([
        'room_id' => $lab->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Mouse hilang baru lapor',
        'status_repair' => 'Belum Diperbaiki',
        'reported_at' => now(),
    ]);

    // Complaint reported 5 days ago but already fixed
    $resolvedComplaint = Complaint::create([
        'room_id' => $lab->id,
        'reporter_id' => $this->guru->id,
        'description' => 'Lampu kedip sudah diganti',
        'status_repair' => 'Sudah Diperbaiki',
        'reported_at' => now()->subDays(5),
    ]);

    $this->actingAs($this->waka)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Peringatan Pengaduan Belum Ditangani (≥ 3 Hari)')
        ->assertSeeText('Kabel LAN putus belum diganti')
        ->assertSeeText('Hubungi PIC via WhatsApp')
        ->assertSee('wa.me/6285550003333', false)
        ->assertDontSeeText('Mouse hilang baru lapor')
        ->assertDontSeeText('Lampu kedip sudah diganti');
});
