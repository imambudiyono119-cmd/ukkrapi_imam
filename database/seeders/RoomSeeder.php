<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $pic1 = User::where('username', 'pic1')->firstOrFail();
        $pic2 = User::where('username', 'pic2')->firstOrFail();
        $pic3 = User::where('username', 'pic3')->orWhere('username', 'udinfrimawan')->first() ?? $pic1;

        $rooms = [
            ['room_name' => 'Ruang Multimedia', 'capacity' => 25, 'location' => 'Lantai 2', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Aula Sekolah', 'capacity' => 80, 'location' => 'Lantai 3', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Ruang OSIS', 'capacity' => 20, 'location' => 'Lantai 3', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Lapangan', 'capacity' => 300, 'location' => 'Area luar', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Masjid', 'capacity' => 150, 'location' => 'Area sekolah', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Parkiran Depan', 'capacity' => 100, 'location' => 'Area depan sekolah', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Parkiran Belakang', 'capacity' => 100, 'location' => 'Area belakang sekolah', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Perpustakaan', 'capacity' => 60, 'location' => 'Area sekolah', 'pic_id' => $pic2->id, 'is_active' => true],
            ['room_name' => 'Ruang Wijaya Kusuma', 'capacity' => 60, 'location' => 'Gedung Utama Lantai 2', 'pic_id' => $pic2->id, 'is_active' => true],
        ];

        // pic1: khusus ruang kelas / teori
        for ($number = 1; $number <= 18; $number++) {
            $rooms[] = [
                'room_name' => 'Ruang Teori '.$number,
                'capacity' => 40,
                'location' => 'Lantai 1',
                'pic_id' => $pic1->id,
                'is_active' => true,
            ];
        }

        // pic3 (Udin Frimawan): khusus seluruh lab
        for ($number = 1; $number <= 19; $number++) {
            $rooms[] = [
                'room_name' => 'Lab Komputer '.$number,
                'capacity' => 30,
                'location' => 'Lantai 2',
                'pic_id' => $pic3->id,
                'is_active' => true,
            ];
        }

        foreach ($rooms as $room) {
            Room::updateOrCreate(['room_name' => $room['room_name']], $room);
        }
    }
}
