# Spesifikasi Kebutuhan Perangkat Lunak

## RAPI-Sekolah

**Sistem Informasi Manajemen Agenda dan Sarana Prasarana Sekolah**

| Informasi | Keterangan |
|---|---|
| Versi dokumen | 1.0 |
| Status | Draf untuk UKK |
| Tanggal | 2 November 2026 |
| Disiapkan oleh | [Nama Peserta Didik] |
| Sekolah | [Nama Sekolah] |

> Dokumen ini mengikuti susunan umum dokumen SRS pada referensi SIMJARU yang diberikan. Isi kebutuhan disesuaikan dengan aplikasi RAPI-Sekolah pada proyek UKK ini. Identitas peserta didik dan sekolah perlu dilengkapi.

## Riwayat Revisi

| Versi | Tanggal | Keterangan |
|---|---|---|
| 1.0 | 2 November 2026 | Penyusunan draf awal SRS untuk proyek UKK RAPI-Sekolah. |

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum](#2-deskripsi-umum)
3. [Kebutuhan Antarmuka Eksternal](#3-kebutuhan-antarmuka-eksternal)
4. [Kebutuhan Fungsional](#4-kebutuhan-fungsional)
5. [Kebutuhan Nonfungsional](#5-kebutuhan-nonfungsional)
6. [Kebutuhan Data](#6-kebutuhan-data)
7. [Batasan, Asumsi, dan Catatan Implementasi](#7-batasan-asumsi-dan-catatan-implementasi)
8. [Kriteria Penerimaan](#8-kriteria-penerimaan)

# 1. Pendahuluan

## 1.1 Tujuan

Dokumen ini menjelaskan kebutuhan perangkat lunak RAPI-Sekolah sebagai acuan perancangan, implementasi, pengujian, dan evaluasi proyek UKK. Dokumen mencakup pengelolaan agenda sekolah dan ruang, pengajuan serta persetujuan jadwal, dan pelaporan kerusakan sarana prasarana.

## 1.2 Ruang Lingkup

RAPI-Sekolah adalah aplikasi web untuk membantu warga sekolah melihat jadwal kegiatan dan mengelola informasi kegiatan, ruangan, serta pengaduan fasilitas. Aplikasi menyediakan akses berbasis peran, kalender publik, dashboard setelah login, dan pengingat penanganan pengaduan melalui tautan WhatsApp.

Sistem ini mendukung:

- Kalender kegiatan sekolah yang dapat dilihat tanpa login.
- Pengajuan kegiatan dan peminjaman ruang oleh Guru/Organisasi.
- Peninjauan, keputusan, dan perubahan jadwal oleh Waka/Admin.
- Pengelolaan akun anggota tertentu oleh Waka/Admin.
- Pengiriman pengaduan kerusakan oleh pengguna yang sudah login.
- Pembaruan status perbaikan oleh Waka/Admin atau PIC ruang terkait.
- Pembuatan tautan WhatsApp untuk eskalasi pengaduan oleh Waka/Admin.

Sistem tidak mengirim pesan WhatsApp secara otomatis. Sistem menyiapkan tautan yang membuka WhatsApp agar pengguna dapat meninjau dan mengirim pesannya sendiri.

## 1.3 Pengguna Dokumen

Dokumen ini ditujukan untuk:

- Peserta didik sebagai pengembang proyek UKK.
- Guru pembimbing dan penguji UKK.
- Perwakilan sekolah yang bertindak sebagai Waka/Admin, PIC Ruang, Guru/Organisasi, atau Siswa/Tamu.
- Penguji perangkat lunak yang memeriksa pemenuhan kebutuhan.

## 1.4 Definisi dan Istilah

| Istilah | Definisi |
|---|---|
| RAPI-Sekolah | Sistem Informasi Manajemen Agenda dan Sarana Prasarana Sekolah. |
| UKK | Uji Kompetensi Keahlian. |
| Waka/Admin | Pengguna yang mengelola keputusan agenda dan fungsi administrasi. |
| PIC Ruang | Pengguna yang bertanggung jawab atas ruang tertentu dan penanganan fasilitasnya. |
| Guru/Organisasi | Pengguna yang dapat mengajukan kegiatan sekolah. |
| Siswa/Tamu | Pengguna yang dapat mengakses fungsi yang diberikan kepada akun/perannya, termasuk pelaporan pengaduan setelah login. |
| Agenda | Kegiatan yang dijadwalkan pada tanggal, waktu, dan ruang tertentu. |
| Pengaduan | Laporan kerusakan atau masalah sarana prasarana di suatu ruang. |
| SRS | Software Requirements Specification atau Spesifikasi Kebutuhan Perangkat Lunak. |

## 1.5 Referensi

- Dokumen contoh SRS SIMJARU yang diberikan sebagai referensi struktur.
- Implementasi aplikasi RAPI-Sekolah pada repositori proyek UKK.
- README proyek RAPI-Sekolah.

# 2. Deskripsi Umum

## 2.1 Perspektif Produk

RAPI-Sekolah merupakan aplikasi web berbasis Laravel. Aplikasi menggunakan basis data relasional untuk menyimpan akun, peran, ruang, agenda, pengaduan, dan notifikasi. Kalender publik dapat dibuka melalui peramban, sementara fungsi pengelolaan memerlukan autentikasi dan dibatasi berdasarkan peran pengguna.

## 2.2 Fungsi Produk

Fungsi utama sistem meliputi:

1. Menampilkan kalender dan informasi agenda.
2. Mengautentikasi pengguna dan menyediakan pemulihan kata sandi melalui email.
3. Menyajikan dashboard setelah pengguna login.
4. Menerima pengajuan agenda serta mencegah benturan waktu pada ruang yang sama.
5. Memungkinkan Waka/Admin menyetujui atau menolak pengajuan dan mengubah jadwal.
6. Menyimpan perubahan status agenda dan menampilkan notifikasi terkait.
7. Menerima pengaduan fasilitas beserta foto opsional.
8. Mengelola status penanganan pengaduan sesuai kewenangan.
9. Membuat tautan WhatsApp untuk membantu eskalasi pengaduan.
10. Memungkinkan Waka/Admin menambah akun anggota serta memperbarui email pemulihan atau kata sandi anggota.

## 2.3 Karakteristik Pengguna

| Peran | Tanggung jawab dan hak akses utama |
|---|---|
| Pengunjung publik | Melihat kalender tanpa login. |
| Waka/Admin | Meninjau, menyetujui, atau menolak pengajuan agenda; mengubah agenda; mengelola akun anggota; memperbarui status pengaduan; dan membuka tautan eskalasi WhatsApp. |
| PIC Ruang | Melihat informasi aplikasi dan memperbarui status pengaduan untuk ruang yang menjadi tanggung jawabnya. |
| Guru/Organisasi | Mengajukan kegiatan, melihat status dan notifikasi jadwal, serta menggunakan fungsi pengaduan yang tersedia bagi pengguna login. |
| Siswa/Tamu | Menggunakan fungsi yang diizinkan untuk akun login, termasuk menyampaikan pengaduan fasilitas. |

## 2.4 Lingkungan Operasi

- Aplikasi berjalan pada server yang mendukung PHP 8.4 dan Laravel 13.
- Basis data yang digunakan proyek adalah MySQL.
- Pengguna mengakses aplikasi melalui peramban web modern pada komputer atau perangkat seluler.
- Tampilan dirancang dengan pendekatan mobile-first.
- Koneksi internet diperlukan untuk mengakses aplikasi serta membuka layanan email atau WhatsApp eksternal.

## 2.5 Batasan Desain dan Implementasi

- Hak akses ditentukan berdasarkan peran akun.
- Agenda harus memiliki ruang, nama kegiatan, tanggal, waktu mulai, dan waktu selesai.
- Waktu selesai harus lebih akhir daripada waktu mulai.
- Agenda yang diajukan atau disetujui tidak boleh bentrok pada ruang dan waktu yang sama.
- Kata sandi disimpan dalam bentuk hash dan tidak ditampilkan kembali kepada pengguna.
- Foto pengaduan bersifat opsional; format yang diterima adalah JPG, JPEG, PNG, atau WEBP dengan ukuran maksimum 5 MB.
- Pengiriman pesan WhatsApp bergantung pada aplikasi/peramban WhatsApp dan tindakan pengguna.

# 3. Kebutuhan Antarmuka Eksternal

## 3.1 Antarmuka Pengguna

Antarmuka menyediakan:

- Halaman kalender publik untuk melihat agenda.
- Halaman login, lupa kata sandi, dan pengaturan ulang kata sandi.
- Dashboard setelah login dengan informasi yang relevan bagi pengguna.
- Formulir pengajuan agenda untuk peran yang berwenang.
- Formulir pengaduan yang memuat pilihan ruang, deskripsi, dan foto opsional.
- Daftar pengaduan dan kontrol perubahan status sesuai hak akses.
- Halaman administrasi anggota untuk Waka/Admin.
- Tampilan yang dapat digunakan pada layar ponsel maupun komputer.

Pesan validasi dan konfirmasi ditampilkan dalam Bahasa Indonesia.

## 3.2 Antarmuka Perangkat Keras

Tidak diperlukan perangkat keras khusus. Pengguna memerlukan perangkat yang dapat menjalankan peramban web dan terhubung ke jaringan.

## 3.3 Antarmuka Perangkat Lunak

- Sistem operasi server yang kompatibel dengan PHP dan Laravel.
- PHP 8.4 dan Laravel 13.
- MySQL sebagai basis data aplikasi.
- Peramban web modern dengan dukungan JavaScript.
- Layanan email yang dikonfigurasi untuk pengiriman tautan pemulihan kata sandi.
- WhatsApp atau WhatsApp Web untuk membuka dan mengirim pesan eskalasi.

## 3.4 Antarmuka Komunikasi

- Pengguna mengakses aplikasi melalui HTTP atau HTTPS sesuai konfigurasi server.
- Permintaan web menggunakan rute aplikasi Laravel.
- Kalender menyediakan data agenda dalam format JSON untuk ditampilkan pada antarmuka kalender.
- Pemulihan kata sandi menggunakan tautan yang dikirim ke alamat email akun.
- Eskalasi pengaduan membuka URL WhatsApp dengan nomor PIC dan teks pesan yang telah disiapkan.

# 4. Kebutuhan Fungsional

## 4.1 Autentikasi dan Pengelolaan Sesi

| ID | Kebutuhan |
|---|---|
| FR-01 | Sistem harus menyediakan halaman login menggunakan username dan kata sandi. |
| FR-02 | Sistem harus menolak login jika kredensial tidak valid dan menampilkan pesan kesalahan yang sesuai. |
| FR-03 | Sistem harus membatasi percobaan login dan permintaan pemulihan kata sandi sesuai aturan pembatasan akses aplikasi. |
| FR-04 | Sistem harus menyediakan fungsi logout dan mengakhiri sesi pengguna. |
| FR-05 | Sistem harus menyediakan permintaan tautan pengaturan ulang kata sandi melalui email. |
| FR-06 | Sistem harus menerima kata sandi baru yang memenuhi panjang minimum 8 karakter dan konfirmasi yang cocok. |
| FR-07 | Sistem harus menyediakan pembaruan nama, nomor WhatsApp, dan kata sandi pada profil pengguna sesuai validasi yang berlaku. |

## 4.2 Kalender Publik

| ID | Kebutuhan |
|---|---|
| FR-08 | Sistem harus menampilkan halaman kalender yang dapat diakses tanpa login. |
| FR-09 | Kalender harus menampilkan nama kegiatan serta waktu pelaksanaan dan informasi ruang yang tersedia. |
| FR-10 | Sistem harus menyediakan data agenda kalender dalam format yang dapat dibaca antarmuka kalender. |

## 4.3 Pengajuan dan Pengelolaan Agenda

| ID | Kebutuhan |
|---|---|
| FR-11 | Guru/Organisasi harus dapat mengajukan agenda dengan memilih ruang, mengisi nama kegiatan, tanggal, waktu mulai, dan waktu selesai. |
| FR-12 | Sistem harus menolak agenda dengan tanggal sebelum tanggal berjalan atau waktu selesai yang tidak lebih akhir dari waktu mulai. |
| FR-13 | Sistem harus memeriksa benturan waktu pada ruang dan tanggal yang sama terhadap agenda berstatus diajukan atau disetujui. |
| FR-14 | Sistem harus menyimpan pengajuan Guru/Organisasi dengan status awal “diajukan”. |
| FR-15 | Agenda yang dibuat Waka/Admin dapat langsung disimpan dengan status “disetujui”. |
| FR-16 | Waka/Admin harus dapat menyetujui atau menolak agenda berstatus “diajukan”. |
| FR-17 | Sistem harus mencatat Waka/Admin yang menyetujui agenda. |
| FR-18 | Waka/Admin harus dapat mengubah ruang, nama, tanggal, waktu, dan status agenda dengan validasi benturan jadwal. |
| FR-19 | Sistem harus menyimpan alasan pemindahan ruang bila alasan diberikan pada perubahan agenda. |
| FR-20 | Sistem harus membuat notifikasi bagi pemohon Guru/Organisasi ketika pengajuan disetujui, ditolak, atau jadwalnya berubah. |

## 4.4 Dashboard dan Notifikasi

| ID | Kebutuhan |
|---|---|
| FR-21 | Sistem harus menampilkan ringkasan agenda hari ini, pengaduan yang belum selesai, ruang, dan informasi lain yang relevan pada dashboard. |
| FR-22 | Dashboard Waka/Admin harus menampilkan pengajuan agenda yang menunggu keputusan. |
| FR-23 | Sistem harus menyimpan notifikasi perubahan jadwal dan menyediakan notifikasi tersebut kepada pengguna terkait. |

## 4.5 Pengelolaan Akun Anggota

| ID | Kebutuhan |
|---|---|
| FR-24 | Waka/Admin harus dapat menambahkan akun anggota dengan nama lengkap, username unik, email, peran, kata sandi, dan nomor WhatsApp opsional. |
| FR-25 | Sistem harus membatasi pembuatan akun anggota pada peran PIC Ruang dan Guru/Organisasi melalui halaman administrasi anggota. |
| FR-26 | Waka/Admin harus dapat memperbarui email pemulihan dan mengatur ulang kata sandi anggota. |
| FR-27 | Sistem tidak boleh menampilkan kata sandi anggota yang tersimpan. |

## 4.6 Pengaduan Sarana Prasarana

| ID | Kebutuhan |
|---|---|
| FR-28 | Pengguna yang sudah login harus dapat membuat pengaduan dengan memilih ruang dan mengisi deskripsi kerusakan. |
| FR-29 | Pengguna dapat melampirkan satu foto bukti dengan format dan ukuran yang didukung. |
| FR-30 | Sistem harus mencatat pelapor, ruang, deskripsi, waktu pelaporan, foto opsional, dan status awal “Belum Diperbaiki”. |
| FR-31 | Waka/Admin harus dapat memperbarui status pengaduan. |
| FR-32 | PIC Ruang hanya boleh memperbarui status pengaduan pada ruang yang menjadi tanggung jawabnya. |
| FR-33 | Status pengaduan yang diizinkan adalah “Belum Diperbaiki”, “Menunggu Spare Part”, dan “Sudah Diperbaiki”. |

## 4.7 Eskalasi WhatsApp

| ID | Kebutuhan |
|---|---|
| FR-34 | Waka/Admin harus dapat membuka tautan WhatsApp untuk mengingatkan PIC terkait pengaduan. |
| FR-35 | Sistem harus menyiapkan nomor WhatsApp PIC dan pesan yang menyebut ruang terkait bila nomor tersedia. |
| FR-36 | Sistem tidak boleh menyatakan pesan telah terkirim hanya karena tautan WhatsApp berhasil dibuka. |
| FR-37 | Batas hari eskalasi awal dikonfigurasi dengan nilai bawaan 3 hari dan dapat disesuaikan oleh pengelola sistem. |

# 5. Kebutuhan Nonfungsional

## 5.1 Keamanan

- Fungsi pengelolaan harus memerlukan autentikasi.
- Sistem harus memeriksa otorisasi peran pada tindakan yang dibatasi.
- Sistem harus memvalidasi data masukan di sisi server.
- Kata sandi harus disimpan menggunakan hashing, bukan teks biasa.
- Formulir yang mengubah data harus dilindungi dari permintaan lintas situs sesuai mekanisme framework.
- Berkas foto harus dibatasi pada format gambar dan ukuran yang telah ditentukan.
- Informasi rahasia seperti kata sandi tidak boleh ditampilkan dalam halaman atau daftar akun.

## 5.2 Kemudahan Penggunaan

- Antarmuka harus menggunakan Bahasa Indonesia yang jelas.
- Formulir harus memberi label yang dapat dipahami dan menampilkan kesalahan validasi di dekat konteksnya.
- Fungsi utama harus dapat digunakan pada layar ponsel.
- Kalender publik harus dapat dibuka tanpa proses login.

## 5.3 Keandalan dan Integritas Data

- Sistem harus menjaga konsistensi relasi agenda dengan ruang dan pemohon.
- Sistem harus mencegah penyimpanan agenda yang bentrok berdasarkan ruang, tanggal, dan rentang waktu.
- Perubahan status agenda dan pengaduan hanya boleh memakai nilai status yang telah ditentukan.
- Pengelola server harus menyediakan pencadangan basis data dan berkas unggahan secara berkala.

## 5.4 Kompatibilitas dan Pemeliharaan

- Aplikasi harus dapat dijalankan pada lingkungan yang mendukung PHP 8.4, Laravel 13, dan MySQL.
- Antarmuka harus mendukung peramban web modern dengan JavaScript aktif.
- Pengaturan ambang eskalasi harus dapat diubah melalui konfigurasi lingkungan tanpa mengubah logika bisnis utama.

## 5.5 Kinerja

- Halaman dan data kalender harus tetap dapat digunakan pada koneksi internet sekolah yang tersedia.
- Waktu respons dan kapasitas pengguna bersamaan belum ditentukan melalui pengujian beban pada proyek ini; nilai target perlu ditetapkan berdasarkan lingkungan pemasangan sekolah.

# 6. Kebutuhan Data

## 6.1 Entitas Utama

| Entitas | Data utama |
|---|---|
| Pengguna | Username, nama lengkap, email, kata sandi tersimpan secara aman, nomor WhatsApp, dan peran. |
| Peran | Nama peran pengguna, seperti Waka/Admin, PIC Ruang, Guru/Organisasi, dan Siswa/Tamu. |
| Ruang | Nama ruang, kapasitas, lokasi, PIC, dan status aktif. |
| Agenda | Ruang, pemohon/PIC, nama kegiatan, tanggal, waktu mulai, waktu selesai, status, pemberi persetujuan, dan alasan pemindahan ruang. |
| Pengaduan | Ruang, pelapor, deskripsi, lokasi foto opsional, status perbaikan, PIC penangan, dan waktu pelaporan. |
| Notifikasi | Pengguna penerima, jenis notifikasi, data perubahan agenda, dan status baca. |

## 6.2 Aturan Data

- Username harus unik.
- Nama ruang harus unik.
- Setiap agenda harus terhubung ke satu ruang dan satu pemohon/penanggung jawab.
- Agenda dapat memiliki pemberi persetujuan dan alasan pemindahan ruang yang kosong.
- Setiap pengaduan harus terhubung ke satu ruang dan satu pelapor.
- Foto dan PIC penangan pengaduan bersifat opsional.
- Status agenda harus berupa “diajukan”, “disetujui”, atau “ditolak”.
- Status perbaikan harus berupa salah satu dari tiga status yang ditentukan pada FR-33.

# 7. Batasan, Asumsi, dan Catatan Implementasi

## 7.1 Asumsi

- Sekolah menyediakan data akun, data ruang, penanggung jawab ruang, dan alamat email yang valid.
- Server memiliki koneksi yang diperlukan untuk mengirim email pemulihan kata sandi.
- Pengguna memiliki perangkat dan jaringan untuk mengakses aplikasi.
- Nomor WhatsApp PIC disimpan dalam format internasional agar dapat digunakan pada tautan WhatsApp.
- Ambang eskalasi bawaan adalah 3 hari; sekolah dapat menetapkan nilai lain sesuai kebijakan.

## 7.2 Batasan Lingkup

- Aplikasi menyiapkan tautan WhatsApp, bukan integrasi pengiriman pesan otomatis.
- Dokumen ini tidak menetapkan format laporan cetak atau ekspor karena fungsi tersebut tidak ditemukan sebagai fitur utama pada implementasi yang ditinjau.
- Pengelolaan CRUD ruang secara lengkap belum tersedia pada antarmuka/rute aplikasi yang ditinjau dan perlu dipastikan sebagai kebutuhan lanjutan bila dibutuhkan untuk operasional.

## 7.3 Catatan Kesesuaian Implementasi

Dokumen ini merangkum kebutuhan dan kondisi kode proyek yang ditinjau. Beberapa kebutuhan mencerminkan perilaku yang dituju, tetapi belum seluruhnya konsisten pada semua antarmuka. Sebelum aplikasi digunakan secara operasional, pengembang perlu memverifikasi khususnya:

- Hak akses melihat daftar pengaduan agar sesuai dengan peran dan kebijakan privasi sekolah.
- Penerapan ambang 3 hari pada alur eskalasi, bukan hanya ketersediaan nilai konfigurasi.
- Ketersediaan antarmuka untuk mengelola data ruang dan penugasan PIC.
- Pencatatan dan tampilan alasan pemindahan ruang pada seluruh alur yang relevan.

# 8. Kriteria Penerimaan

Proyek dapat dinyatakan memenuhi kebutuhan utama apabila:

1. Pengunjung dapat membuka kalender publik tanpa login.
2. Pengguna dapat login dan hanya mengakses fungsi yang diizinkan untuk perannya.
3. Guru/Organisasi dapat mengajukan agenda dan sistem menolak waktu selesai yang tidak valid atau jadwal yang bentrok.
4. Waka/Admin dapat memproses pengajuan agenda dan pemohon menerima notifikasi atas keputusan atau perubahan jadwal.
5. Pengguna login dapat membuat pengaduan dengan deskripsi dan foto opsional yang memenuhi batas format serta ukuran.
6. PIC hanya dapat mengubah status pengaduan pada ruang yang menjadi tanggung jawabnya, sedangkan Waka/Admin dapat mengelola status pengaduan.
7. Status pengaduan hanya dapat diubah ke salah satu nilai yang telah ditentukan.
8. Waka/Admin dapat membuka tautan WhatsApp dengan nomor PIC dan pesan pengingat bila nomor tersedia.
9. Waka/Admin dapat menambahkan akun anggota pada peran yang diizinkan serta memperbarui email pemulihan atau kata sandinya.
10. Halaman utama dapat digunakan pada perangkat seluler dan menampilkan pesan validasi yang dapat dipahami.
