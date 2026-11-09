<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EventBookingService
{
    public function hasConflict(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        return Event::query()
            ->where('room_id', $roomId)
            ->where('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->whereIn('status', ['diajukan', 'disetujui'])
            ->when($excludeId, fn ($query) => $query->whereKey('!=', $excludeId))
            ->exists();
    }

    public function create(array $data): Event
    {
        $roomId = (int) ($data['room_id'] ?? 0);
        $date = $data['date'] ?? '';
        $start = $data['start_time'] ?? '';
        $end = $data['end_time'] ?? '';

        if ($this->hasConflict($roomId, $date, $start, $end)) {
            $conflict = Event::query()
                ->where('room_id', $roomId)
                ->where('date', $date)
                ->where(function ($query) use ($start, $end) {
                    $query->where('start_time', '<', $end)
                        ->where('end_time', '>', $start);
                })
                ->whereIn('status', ['diajukan', 'disetujui'])
                ->first();

            throw new InvalidArgumentException('Agenda yang bentrok: '.($conflict?->event_name ?? 'agenda').' ('.($conflict?->start_time ?? '').' - '.($conflict?->end_time ?? '').').');
        }

        return DB::transaction(function () use ($data) {
            return Event::create($data);
        });
    }

    public function moveRoom(Event $event, int $newRoomId, string $reason): Event
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new InvalidArgumentException('Alasan pindah ruang wajib diisi.');
        }

        if ($this->hasConflict($newRoomId, $event->date, $event->start_time, $event->end_time, $event->id)) {
            $conflict = Event::query()
                ->where('room_id', $newRoomId)
                ->where('date', $event->date)
                ->where(function ($query) use ($event) {
                    $query->where('start_time', '<', $event->end_time)
                        ->where('end_time', '>', $event->start_time);
                })
                ->whereKey('!=', $event->id)
                ->whereIn('status', ['diajukan', 'disetujui'])
                ->first();

            throw new InvalidArgumentException('Ruang tujuan bentrok dengan agenda '.($conflict?->event_name ?? 'lainnya').'.');
        }

        $event->room_id = $newRoomId;
        $event->move_reason = $reason;
        $event->save();

        return $event;
    }
}
