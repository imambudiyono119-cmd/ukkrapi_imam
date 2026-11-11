<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\NationalHolidayCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar');
    }

    public function api(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $events = Event::with(['room', 'pic.role'])
            ->where('status', '!=', 'ditolak')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('event_name', 'like', '%'.$search.'%')
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('room_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('pic', fn ($picQuery) => $picQuery->where('fullname', 'like', '%'.$search.'%'));
                });
            })
            ->get()
            ->map(function ($event) {
                $bookerRole = 'Umum';
                $bookerType = 'umum';
                $color = '#0284c7';
                $borderColor = '#0369a1';

                if ($event->pic?->isAdmin()) {
                    $bookerRole = 'Waka/Admin';
                    $bookerType = 'waka';
                    $color = '#4f46e5'; // Indigo untuk Waka
                    $borderColor = '#3730a3';
                } elseif ($event->pic?->isTeacher() || $event->pic?->role?->role_name === 'Guru') {
                    $bookerRole = 'Guru';
                    $bookerType = 'guru';
                    $color = '#0284c7'; // Biru untuk Guru
                    $borderColor = '#0369a1';
                } elseif ($event->pic?->isStudent() || $event->pic?->isOrganization() || in_array($event->pic?->role?->role_name, ['Organisasi', 'Siswa/Tamu'], true)) {
                    $bookerRole = 'Siswa / Organisasi';
                    $bookerType = 'siswa';
                    $color = '#059669'; // Hijau untuk Siswa/OSIS
                    $borderColor = '#047857';
                }

                $calendarEvent = [
                    'id' => $event->id,
                    'title' => $event->event_name,
                    'start' => $event->date.'T'.$event->start_time,
                    'end' => $event->date.'T'.$event->end_time,
                    'room_id' => $event->room_id,
                    'room_name' => $event->room?->room_name,
                    'room_location' => $event->room?->location,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'status' => $event->status,
                    'backgroundColor' => $color,
                    'borderColor' => $borderColor,
                    'textColor' => '#ffffff',
                    'booker_type' => $bookerType,
                    'booker_role' => $bookerRole,
                    'classNames' => ['event-'.$bookerType],
                ];

                if (! $event->pic?->isAdmin()) {
                    $calendarEvent['penanggung_jawab'] = $event->pic?->fullname;
                }

                if (auth()->check()) {
                    $calendarEvent['report_url'] = route('complaints.index', ['event_id' => $event->id]);
                    $calendarEvent['can_cancel'] = auth()->user()->isAdmin() || $event->pic_id === auth()->id();
                    $calendarEvent['cancel_url'] = route('events.destroy', $event);
                }

                return $calendarEvent;
            });

        return response()->json($events);
    }

    public function holidays(NationalHolidayCalendar $holidays): JsonResponse
    {
        return response()->json($holidays->events());
    }
}
