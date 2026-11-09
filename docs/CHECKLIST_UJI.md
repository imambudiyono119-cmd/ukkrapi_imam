# Checklist Uji Manual

## Skenario uji

1. Login dengan akun `waka` / `password123` dan verifikasi dashboard menampilkan data.
2. Login sebagai `pic1` lalu ubah status pengaduan ruang yang dikelola PIC.
3. Login sebagai `guru1` dan kirim pengaduan baru dengan foto valid.
4. Coba kirim pengaduan dengan file non-gambar, pastikan validasi gagal.
5. Ajukan agenda baru pada ruang yang sama dengan jam yang overlap, lalu pastikan sistem menolak.
6. Ajukan agenda baru dengan jam yang bersentuhan tepat di akhir/beginning, lalu pastikan diterima.
7. Pindahkan ruangan agenda oleh Waka dengan alasan, lalu verifikasi `move_reason` terisi.
8. Buka halaman `/kalender` tanpa login dan cek kalender read-only.
9. Cek halaman `/api/events` mengembalikan JSON dengan field `title`, `start`, `end`, `room_name`, dan `penanggung_jawab`.
10. Uji WhatsApp link normalisasi nomor `0` menjadi `62` dan buka link di tab baru.

## Pemetaan rubrik

| Komponen | Bobot | Fokus |
| --- | --- | --- |
| Persiapan | 10% | .env, database, migrate, seed |
| Coding | 40% | model, service, policy, controller |
| UI-UX | 30% | layout mobile-first, dashboard, kalender |
| Sikap & Waktu | 20% | pengerjaan teratur, validasi, dokumentasi |

## File utama

- `app/Models/User.php`
- `app/Models/Event.php`
- `app/Models/Complaint.php`
- `app/Services/EventBookingService.php`
- `app/Policies/ComplaintPolicy.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `database/seeders/*.php`
- `README.md`
