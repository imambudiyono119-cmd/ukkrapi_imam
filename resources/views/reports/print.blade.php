<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Periode {{ $periodLabel }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 24px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            font-weight: 800;
        }
        .header h2 {
            font-size: 14px;
            margin: 4px 0 0;
            font-weight: 600;
            color: #475569;
        }
        .header p {
            font-size: 11px;
            margin: 4px 0 0;
            color: #64748b;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #334155;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        tfoot tr td {
            font-weight: 700;
            background-color: #f8fafc;
        }
        .signature-section {
            margin-top: 36px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            text-align: center;
            width: 250px;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background-color: #0284c7;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary {
            background-color: #64748b;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('reports.monthly', ['month' => $monthStart->format('Y-m')]) }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="header">
        <h1>SMK Negeri 1 Bantul</h1>
        <h2>Laporan Bulanan Peminjaman Ruang &amp; Fasilitas Sekolah (RAPI)</h2>
        <p>Jl. Parangtritis No.KM.11, Sabdodadi, Bantul, D.I. Yogyakarta 55715</p>
    </div>

    <div class="meta-info">
        <div>
            <strong>Periode Laporan:</strong> {{ $periodLabel }}<br>
            @if ($search)
                <strong>Filter Pencarian:</strong> "{{ $search }}"
            @endif
        </div>
        <div style="text-align: right;">
            <strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
            <strong>Total Peminjaman:</strong> {{ count($events) }} Kegiatan
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Kegiatan</th>
                <th>Ruang / Fasilitas</th>
                <th style="width: 90px;">Tanggal</th>
                <th style="width: 100px;">Waktu</th>
                <th>Pemohon</th>
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $index => $event)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $event->event_name }}</strong></td>
                    <td>{{ $event->room?->room_name ?? 'Ruang tidak tersedia' }}</td>
                    <td class="text-center">{{ \Carbon\CarbonImmutable::parse($event->date)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ substr($event->start_time, 0, 5) }}–{{ substr($event->end_time, 0, 5) }} WIB</td>
                    <td>{{ $event->pic?->fullname ?? 'Pengguna tidak tersedia' }}</td>
                    <td class="text-center" style="color: #047857; font-weight: 600;">Disetujui</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">
                        Tidak ada peminjaman disetujui pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTAL PEMINJAMAN DISETUJUI</td>
                <td colspan="4">{{ count($events) }} Kegiatan</td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Bantul, {{ now()->translatedFormat('d F Y') }}<br>Waka Sarana &amp; Prasarana,</p>
            <div style="height: 60px;"></div>
            <p><strong>{{ auth()->user()?->fullname ?? 'Administrator' }}</strong></p>
        </div>
    </div>
</body>
</html>

