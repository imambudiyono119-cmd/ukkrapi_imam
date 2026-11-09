# RAPI-Sekolah

Aplikasi manajemen agenda, peminjaman ruangan, pemantauan fasilitas, dan pengaduan kerusakan sarana prasarana sekolah dengan pendekatan mobile-first.

## Fitur utama

- Login satu pintu dengan pembagian role Waka/Admin, PIC Ruang, Guru/Organisasi, dan Siswa/Tamu
- Kalender publik read-only berbasis FullCalendar
- Dashboard per role
- Pengelolaan ruangan dan penugasan PIC
- Peminjaman agenda dengan validasi tabrakan waktu
- Pemindahan ruangan oleh Waka dengan alasan
- Pengaduan fasilitas dengan status perbaikan
- Peringatan WhatsApp untuk pengaduan yang belum selesai

## Teknologi

- Laravel 13
- Blade + Tailwind CDN
- FullCalendar
- MySQL (database yang tersedia di lingkungan ini)

## Instalasi

1. Salin `.env.example` ke `.env` dan sesuaikan konfigurasi database bila diperlukan.
2. Jalankan:

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

3. Akses aplikasi di http://localhost:8000

## Akun demo

| Username | Password | Role |
| --- | --- | --- |
| waka | password123 | Waka/Admin |
| pic1 | password123 | PIC Ruang |
| pic2 | password123 | PIC Ruang |
| guru1 | password123 | Guru/Organisasi |
| guru2 | password123 | Guru/Organisasi |
| osis | password123 | Guru/Organisasi |

## Struktur folder

- app/Models: model database dan relasi
- app/Services: logika bisnis seperti `EventBookingService`
- app/Http/Controllers: controller aplikasi
- app/Http/Middleware: otorisasi role
- app/Policies: kebijakan akses pengaduan
- database/seeders: data awal demo
- resources/views: tampilan Blade
- routes/web.php: routing utama

## Implementasi aturan bisnis wajib

1. Anti-tabrakan waktu: `EventBookingService::hasConflict()` dan `Event::scopeOverlapping()`
2. Delegasi PIC: kebijakan akses di `ComplaintPolicy`
3. Status fasilitas: validasi tiga nilai di `Complaint`
4. Eskalasi WhatsApp: helper `App\Helpers\WhatsAppLink`
5. Overriding + notifikasi otomatis: `EventBookingService::moveRoom()` dan data `move_reason`

## [ASUMSI]

- `events.pic_id` dipakai sebagai pemohon atau penanggung jawab kegiatan.
- Alur persetujuan Waka dalam skenario ini adalah Waka dapat meninjau, menyetujui, dan memindahkan ruangan bila diperlukan.
- Batas eskalasi WhatsApp default 3 hari diatur melalui `config/rapi.php` dan variabel `.env` `RAPI_ESCALATION_DAYS`.
- `move_reason` dipakai untuk alasan pemindahan ruangan; istilah `change_reason` tidak dipakai di skema.
- Notifikasi pemindahan ruang disederhanakan tanpa tabel tambahan, dengan `move_reason` yang tampil di dashboard dan riwayat event milik user.

## Risiko / kekurangan yang masih ada

- UI master data yang lengkap masih pada versi minimal untuk kebutuhan inti aplikasi.
- Beberapa fitur lanjutan seperti CRUD penuh dan modal notifikasi kompleks masih disederhanakan agar tetap fungsional dan stabil.

