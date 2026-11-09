<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['username', 'email', 'password', 'fullname', 'phone_number', 'role_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function roomsManaged(): HasMany
    {
        return $this->hasMany(Room::class, 'pic_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'pic_id');
    }

    public function complaintsReported(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    public function complaintsHandled(): HasMany
    {
        return $this->hasMany(Complaint::class, 'handler_id');
    }

    public function isAdmin(): bool
    {
        return $this->role?->role_name === 'Waka/Admin';
    }

    public function isPic(): bool
    {
        return $this->role?->role_name === 'PIC Ruang';
    }

    public function isGuru(): bool
    {
        return in_array($this->role?->role_name, ['Guru', 'Organisasi', 'Guru/Organisasi'], true);
    }

    public function isTeacher(): bool
    {
        return $this->role?->role_name === 'Guru';
    }

    public function isOrganization(): bool
    {
        return $this->role?->role_name === 'Organisasi';
    }

    public function isStudent(): bool
    {
        return $this->role?->role_name === 'Siswa/Tamu';
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->role_name, $roles, true);
    }
}
