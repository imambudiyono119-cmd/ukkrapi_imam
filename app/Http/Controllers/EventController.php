<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Notifications\EventScheduleNotification;
use App\Services\EventBookingService;
use App\Services\NationalHolidayCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    public function store(Request $request, EventBookingService $service, NationalHolidayCalendar $holidays)
    {
        $user = $request->user();
        abort_unless($user?->isAdmin() || $user?->isGuru(), 403, 'Hanya Waka, Guru, atau Organisasi yang dapat menambahkan kegiatan di kalender.');

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
        ], [
            'date.after_or_equal' => 'Tanggal kegiatan tidak boleh sebelum hari ini.',
        ]);

        if ($holidays->isHoliday($validated['date']) && ! $user->isAdmin() && ! $user->isTeacher()) {
            return back()
                ->withErrors(['date' => 'Hanya Guru dan Waka yang dapat mengajukan kegiatan pada hari libur nasional.'])
                ->withInput();
        }

        $validated['pic_id'] = $user->id;
        $validated['status'] = $user->isAdmin() ? 'disetujui' : 'diajukan';

        try {
            $event = $service->create($validated);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['room_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('calendar.public')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, Event $event, EventBookingService $service)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Hanya Waka yang dapat mengubah jadwal kalender.');

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'status' => ['nullable', 'in:diajukan,disetujui'],
            'move_reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'date.after_or_equal' => 'Tanggal kegiatan tidak boleh sebelum hari ini.',
        ]);

        if ($service->hasConflict((int) $validated['room_id'], $validated['date'], $validated['start_time'], $validated['end_time'], $event->id)) {
            return back()->withErrors(['room_id' => 'Jadwal baru bentrok dengan agenda lain di ruangan yang sama.'])->withInput();
        }

        $previousStatus = $event->status;
        $previousSchedule = [
            (int) $event->room_id,
            $event->date,
            substr($event->start_time, 0, 5),
            substr($event->end_time, 0, 5),
        ];

        $event->fill($validated);
        $event->save();

        $scheduleChanged = $previousSchedule !== [
            (int) $event->room_id,
            $event->date,
            substr($event->start_time, 0, 5),
            substr($event->end_time, 0, 5),
        ];

        if ($event->pic?->isGuru()) {
            if ($previousStatus !== $event->status && $event->status === 'disetujui') {
                $event->pic->notify(new EventScheduleNotification($event, 'approved'));
            }

            if ($scheduleChanged) {
                $event->pic->notify(new EventScheduleNotification($event, 'moved'));
            }
        }

        $notifications = Cache::get('global_notifications', []);
        $notifications[] = [
            'message' => 'Jadwal "'.$event->event_name.'" diperbarui pada '.CarbonImmutable::parse($event->date)->format('d-m-Y').' '.$event->start_time.' - '.$event->end_time,
            'created_at' => now()->toDateTimeString(),
        ];
        Cache::put('global_notifications', $notifications, now()->addDays(7));

        return redirect()->route('calendar.public')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function decide(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403, 'Hanya Waka yang dapat memutuskan pengajuan jadwal.');
        abort_unless($event->status === 'diajukan', 409, 'Pengajuan jadwal ini sudah diproses.');

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
        ]);

        $event->status = $validated['status'];
        $event->approved_by = $validated['status'] === 'disetujui' ? $user->id : null;
        $event->rejection_reason = $validated['status'] === 'ditolak' ? $validated['rejection_reason'] : null;
        $event->save();

        if ($event->pic?->isGuru()) {
            $event->pic->notify(new EventScheduleNotification(
                $event,
                $validated['status'] === 'disetujui' ? 'approved' : 'rejected',
            ));
        }

        $decision = $validated['status'] === 'disetujui' ? 'disetujui' : 'ditolak';

        return redirect()->route('dashboard')->with('success', 'Pengajuan jadwal berhasil '.$decision.'.');
    }
}
