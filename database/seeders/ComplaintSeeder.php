<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::where('username', 'guru1')->firstOrFail();
        $pic = User::where('username', 'pic1')->firstOrFail();
        $room = Room::firstOrFail();

        $complaints = [
            ['room_id' => $room->id, 'reporter_id' => $reporter->id, 'description' => 'Lampu ruang tidak menyala', 'photo_path' => null, 'status_repair' => Complaint::STATUS_PENDING, 'handler_id' => $pic->id, 'reported_at' => now()->subDays(5)],
            ['room_id' => Room::skip(1)->first()->id, 'reporter_id' => $reporter->id, 'description' => 'AC berisik saat dinyalakan', 'photo_path' => null, 'status_repair' => Complaint::STATUS_PARTS, 'handler_id' => $pic->id, 'reported_at' => now()->subDays(2)],
            ['room_id' => Room::skip(2)->first()->id, 'reporter_id' => $reporter->id, 'description' => 'Meja rusak pada salah satu kaki', 'photo_path' => null, 'status_repair' => Complaint::STATUS_DONE, 'handler_id' => $pic->id, 'reported_at' => now()->subDays(1)],
            ['room_id' => Room::skip(3)->first()->id, 'reporter_id' => $reporter->id, 'description' => 'Kipas angin mati dan perlu penggantian', 'photo_path' => null, 'status_repair' => Complaint::STATUS_PENDING, 'handler_id' => $pic->id, 'reported_at' => now()->subDays(7)],
            ['room_id' => Room::skip(4)->first()->id, 'reporter_id' => $reporter->id, 'description' => 'Pintu tidak tertutup rapat', 'photo_path' => null, 'status_repair' => Complaint::STATUS_DONE, 'handler_id' => $pic->id, 'reported_at' => now()->subDays(3)],
        ];

        foreach ($complaints as $complaint) {
            Complaint::create($complaint);
        }
    }
}
