<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    public const STATUS_PENDING = 'Belum Diperbaiki';

    public const STATUS_PARTS = 'Menunggu Spare Part';

    public const STATUS_DONE = 'Sudah Diperbaiki';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PARTS,
        self::STATUS_DONE,
    ];

    protected $fillable = [
        'room_id',
        'event_id',
        'reporter_id',
        'description',
        'photo_path',
        'status_repair',
        'handler_id',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $complaint) {
            if (! in_array($complaint->status_repair, self::VALID_STATUSES, true)) {
                throw new \InvalidArgumentException('Status perbaikan tidak valid.');
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function scopeForPic(Builder $query, int $picId): Builder
    {
        return $query->whereHas('room', fn ($roomQuery) => $roomQuery->where('pic_id', $picId));
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status_repair', self::STATUS_PENDING)
            ->where('reported_at', '<=', now()->subDays(config('rapi.escalation_days', 3)));
    }
}
