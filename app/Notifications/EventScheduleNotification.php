<?php

namespace App\Notifications;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Notification;

class EventScheduleNotification extends Notification
{
    public function __construct(public Event $event, public string $action) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->event->loadMissing('room');

        $roomName = $this->event->room?->room_name;
        $roomLocation = $this->event->room?->location;
        $place = implode(', ', array_filter([$roomName, $roomLocation]));

        return [
            'event_id' => $this->event->id,
            'event_name' => $this->event->event_name,
            'action' => $this->action,
            'message' => match ($this->action) {
                'approved' => 'Jadwal "'.$this->event->event_name.'" telah disetujui oleh Waka.',
                'rejected' => 'Pengajuan jadwal "'.$this->event->event_name.'" ditolak oleh Waka. Alasan: '.$this->event->rejection_reason,
                'moved' => 'Jadwal "'.$this->event->event_name.'" dipindahkan ke '.CarbonImmutable::parse($this->event->date)->format('d-m-Y').' pukul '.substr($this->event->start_time, 0, 5).' - '.substr($this->event->end_time, 0, 5).($place !== '' ? ' di '.$place : '').($this->event->move_reason ? '. Alasan: '.$this->event->move_reason : '').'.',
            },
            'rejection_reason' => $this->event->rejection_reason,
            'status' => $this->event->status,
            'date' => $this->event->date,
            'start_time' => $this->event->start_time,
            'end_time' => $this->event->end_time,
            'room_name' => $roomName,
            'room_location' => $roomLocation,
        ];
    }
}
