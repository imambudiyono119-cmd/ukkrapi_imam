<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'room_id',
        'pic_id',
        'event_name',
        'date',
        'start_time',
        'end_time',
        'status',
        'approved_by',
        'move_reason',
        'rejection_reason',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'event_id');
    }

    public function scopeOverlapping(Builder $query, int $roomId, string $date, string $startTime, string $endTime, ?int $excludeId = null): Builder
    {
        return $query
            ->where('room_id', $roomId)
            ->where('date', $date)
            ->where('status', '!=', 'ditolak')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->when($excludeId, fn (Builder $q) => $q->whereKey('!=', $excludeId));
    }
}
