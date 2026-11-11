<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pic = User::whereHas('role', fn ($q) => $q->where('role_name', 'PIC Ruang'))->first();

        Room::firstOrCreate(
            ['room_name' => 'Ruang Wijaya Kusuma'],
            [
                'capacity' => 60,
                'location' => 'Gedung Utama Lantai 2',
                'pic_id' => $pic?->id,
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Room::where('room_name', 'Ruang Wijaya Kusuma')->delete();
    }
};
