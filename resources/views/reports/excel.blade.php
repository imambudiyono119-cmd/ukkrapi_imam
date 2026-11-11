<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000000;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }
        .header-sub {
            font-size: 11pt;
            text-align: center;
        }
        .header-meta {
            font-size: 10pt;
            text-align: center;
            font-style: italic;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        th {
            background-color: #0284c7;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #000000;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }
        td {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 10pt;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .bg-zebra {
            background-color: #f8fafc;
        }
        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .signature-table {
            margin-top: 30px;
            border: none;
            width: 100%;
        }
        .signature-table td {
            border: none;
            padding: 4px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" class="header-title" style="border:none;">LAPORAN BULANAN PEMINJAMAN RUANG &amp; FASILITAS</td>
        </tr>
        <tr>
            <td colspan="7" class="header-sub" style="border:none;">SMK NEGERI 1 BANTUL</td>
        </tr>
        <tr>
            <td colspan="7" class="header-meta" style="border:none;">
                Periode: {{ $monthStart->format('m-Y') }} &bull; Diunduh pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </td>
        </tr>
        <tr>
            <td colspan="7" style="border:none; height: 10px;"></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 220px;">Nama Kegiatan</th>
                <th style="width: 160px;">Ruang / Fasilitas</th>
                <th style="width: 110px;">Tanggal</th>
                <th style="width: 120px;">Waktu</th>
                <th style="width: 180px;">Pemohon / Penanggung Jawab</th>
                <th style="width: 100px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $index => $event)
                <tr class="{{ $index % 2 === 1 ? 'bg-zebra' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $event->event_name }}</td>
                    <td class="text-left">{{ $event->room?->room_name ?? 'Ruang tidak tersedia' }}</td>
                    <td class="text-center">{{ \Carbon\CarbonImmutable::parse($event->date)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ substr($event->start_time, 0, 5) }} &ndash; {{ substr($event->end_time, 0, 5) }} WIB</td>
                    <td class="text-left">{{ $event->pic?->fullname ?? 'Pengguna tidak tersedia' }}</td>
                    <td class="text-center" style="color: #047857; font-weight: bold;">Disetujui</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data peminjaman yang disetujui pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-left" style="font-weight: bold;">TOTAL KEGIATAN DISETUJUI</td>
                <td colspan="4" class="text-left" style="font-weight: bold;">{{ count($events) }} Kegiatan</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table" style="margin-top: 35px;">
        <tr>
            <td style="width: 60%; border:none;"></td>
            <td style="width: 40%; text-align: center; border:none;">
                Bantul, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Waka Sarana &amp; Prasarana</strong><br><br><br><br>
                <u>( {{ auth()->user()?->fullname ?? 'Administrator' }} )</u>
            </td>
        </tr>
    </table>
</body>
</html>
