<?php

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $picRole = Role::firstOrCreate(['role_name' => 'PIC Ruang']);

        // 1. Pastikan pic1 (Khusus Ruang Kelas / Teori) ada
        $pic1 = User::firstOrCreate(
            ['username' => 'pic1'],
            [
                'fullname' => 'PIC 1 (Ruang Teori & Kelas)',
                'phone_number' => '6285550001111',
                'password' => Hash::make('password123'),
                'role_id' => $picRole->id,
            ]
        );
        $pic1->update(['fullname' => 'PIC 1 (Ruang Teori & Kelas)']);

        // 2. Pastikan pic2 (Khusus selain lab dan ruang kelas) ada
        $pic2 = User::firstOrCreate(
            ['username' => 'pic2'],
            [
                'fullname' => 'PIC 2 (Fasilitas Selain Lab & Kelas)',
                'phone_number' => '6285550002222',
                'password' => Hash::make('password123'),
                'role_id' => $picRole->id,
            ]
        );
        $pic2->update(['fullname' => 'PIC 2 (Fasilitas Selain Lab & Kelas)']);

        // 3. Buat / Pastikan Udin Frimawan (pic3) khusus menerima laporan seluruh lab ada
        $pic3 = User::firstOrCreate(
            ['username' => 'pic3'],
            [
                'fullname' => 'Udin Frimawan',
                'phone_number' => '6285550003333',
                'password' => Hash::make('password123'),
                'role_id' => $picRole->id,
            ]
        );

        // Buat alias login udinfrimawan jika belum ada
        User::firstOrCreate(
            ['username' => 'udinfrimawan'],
            [
                'fullname' => 'Udin Frimawan',
                'phone_number' => '6285550003333',
                'password' => Hash::make('password123'),
                'role_id' => $picRole->id,
            ]
        );

        // 4. Update relasi Ruang (Rooms):
        // Seluruh Lab -> PIC Udin Frimawan
        Room::where('room_name', 'like', 'Lab%')
            ->update(['pic_id' => $pic3->id]);

        // Seluruh Ruang Kelas / Teori -> PIC 1
        Room::where('room_name', 'like', 'Ruang Teori%')
            ->update(['pic_id' => $pic1->id]);

        // Fasilitas lainnya selain Lab dan Ruang Teori -> PIC 2
        Room::where('room_name', 'not like', 'Lab%')
            ->where('room_name', 'not like', 'Ruang Teori%')
            ->update(['pic_id' => $pic2->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible jika diperlukan
    }
};
