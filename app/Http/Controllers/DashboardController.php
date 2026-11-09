<?php

namespace App\Http\Controllers;

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

        $eventNotifications = $user->unreadNotifications()
            ->where('type', EventScheduleNotification::class)
            ->latest()
            ->limit(20)
            ->get();
        $eventNotifications->markAsRead();

        $notifications = Cache::get('global_notifications', []);

        return view('dashboard', compact('user', 'notifications', 'pendingScheduleRequests', 'moveRooms', 'eventNotifications'));
    }
}
