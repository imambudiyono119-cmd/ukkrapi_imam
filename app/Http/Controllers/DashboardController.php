<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use App\Notifications\EventScheduleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $pendingScheduleRequests = $user->isAdmin()
            ? Event::with(['room', 'pic'])->where('status', 'diajukan')->latest()->get()
            : collect();

        $moveRooms = $user->isAdmin()
            ? Room::query()->where('is_active', true)->orderBy('room_name')->get(['id', 'room_name'])
            : collect();

        // Pengaduan yang belum selesai dan sudah berumur >= 3 hari untuk dihubungi via WhatsApp oleh Waka
        $overdueComplaints = $user->isAdmin()
            ? Complaint::with(['room.pic', 'reporter'])
                ->where('status_repair', '!=', Complaint::STATUS_DONE)
                ->where(function ($query) {
                    $threshold = now()->subDays(3);
                    $query->where(function ($q) use ($threshold) {
                        $q->whereNotNull('reported_at')->where('reported_at', '<=', $threshold);
                    })->orWhere(function ($q) use ($threshold) {
                        $q->whereNull('reported_at')->where('created_at', '<=', $threshold);
                    });
                })
                ->latest()
                ->get()
            : collect();

        $eventNotifications = $user->unreadNotifications()
            ->where('type', EventScheduleNotification::class)
            ->latest()
            ->limit(20)
            ->get();
        $eventNotifications->markAsRead();

        $notifications = Cache::get('global_notifications', []);

        return view('dashboard', compact(
            'user',
            'notifications',
            'pendingScheduleRequests',
            'moveRooms',
            'overdueComplaints',
            'eventNotifications'
        ));
    }
}
