<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = ['room_name', 'capacity', 'location', 'pic_id', 'is_active'];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'room_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'room_id');
    }
}
