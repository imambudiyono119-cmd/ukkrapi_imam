<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingReportController extends Controller
{
    public function daily(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);
        $date = $validated['date'] ?? today()->toDateString();

        $events = $this->approvedEventsQuery($validated['search'] ?? '')
            ->whereDate('date', $date)
            ->orderBy('start_time')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('reports.bookings', [
            'events' => $events,
            'periodLabel' => CarbonImmutable::parse($date)->format('d-m-Y'),
            'periodType' => 'daily',
            'periodValue' => $date,
            'search' => $validated['search'] ?? '',
            'title' => 'Laporan Peminjaman Harian',
        ]);
    }

    public function monthly(Request $request): View|StreamedResponse
    {
        $validated = $request->validate([
            'month' => ['sometimes', 'required', 'date_format:Y-m'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'download' => ['sometimes', 'in:csv,excel,print'],
        ]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('!Y-m', $month);

        $query = $this->approvedEventsQuery($validated['search'] ?? '')
            ->whereBetween('date', [$monthStart->toDateString(), $monthStart->endOfMonth()->toDateString()]);

        if (($validated['download'] ?? null) === 'excel') {
            $events = $query->orderBy('date')->orderBy('start_time')->orderBy('id')->get();

            return response()->streamDownload(function () use ($events, $monthStart): void {
                echo view('reports.excel', [
                    'events' => $events,
                    'monthStart' => $monthStart,
                ])->render();
            }, 'laporan-peminjaman-'.$month.'.xls', [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="laporan-peminjaman-'.$month.'.xls"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        if (($validated['download'] ?? null) === 'print') {
            $events = $query->orderBy('date')->orderBy('start_time')->orderBy('id')->get();

            return view('reports.print', [
                'events' => $events,
                'periodLabel' => $monthStart->format('m-Y'),
                'monthStart' => $monthStart,
                'search' => $validated['search'] ?? '',
                'title' => 'Laporan Peminjaman Bulanan',
            ]);
        }

        if (($validated['download'] ?? null) === 'csv') {
            return response()->streamDownload(function () use ($query): void {
                $output = fopen('php://output', 'w');
                if ($output === false) {
                    throw new \RuntimeException('Tidak dapat membuat file laporan.');
                }

                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, ['Kegiatan', 'Ruang', 'Tanggal', 'Mulai', 'Selesai', 'Pemohon'], ',', '"', '');

                foreach ($query->orderBy('date')->orderBy('start_time')->orderBy('id')->lazy(200) as $event) {
                    fputcsv($output, [
                        $this->safeCsvValue($event->event_name),
                        $this->safeCsvValue($event->room?->room_name ?? 'Ruang tidak tersedia'),
                        CarbonImmutable::parse($event->date)->format('d-m-Y'),
                        substr($event->start_time, 0, 5),
                        substr($event->end_time, 0, 5),
                        $this->safeCsvValue($event->pic?->fullname ?? 'Pengguna tidak tersedia'),
                    ], ',', '"', '');
                }

                fclose($output);
            }, 'laporan-peminjaman-'.$month.'.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $events = $query
            ->orderBy('date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('reports.bookings', [
            'events' => $events,
            'periodLabel' => $monthStart->format('m-Y'),
            'periodType' => 'monthly',
            'periodValue' => $month,
            'search' => $validated['search'] ?? '',
            'title' => 'Laporan Peminjaman Bulanan',
        ]);
    }

    private function approvedEventsQuery(string $search): Builder
    {
        $search = trim($search);

        return Event::query()
            ->with(['room', 'pic'])
            ->where('status', 'disetujui')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('event_name', 'like', '%'.$search.'%')
                        ->orWhereHas('room', fn (Builder $roomQuery) => $roomQuery->where('room_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('pic', fn (Builder $picQuery) => $picQuery->where('fullname', 'like', '%'.$search.'%'));
                });
            });
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
