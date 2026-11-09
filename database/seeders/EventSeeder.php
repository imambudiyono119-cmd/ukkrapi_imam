<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::where('username', 'guru1')->firstOrFail();
        $rooms = Room::all();
        $base = Carbon::now()->startOfMonth();

        $events = [
            ['room' => $rooms[0]->id, 'event_name' => 'Ujian Tengah Semester', 'date' => $base->copy()->addDays(2), 'start' => '08:00:00', 'end' => '10:00:00', 'status' => 'disetujui'],
            ['room' => $rooms[1]->id, 'event_name' => 'Rapat MGMP', 'date' => $base->copy()->addDays(3), 'start' => '09:00:00', 'end' => '11:00:00', 'status' => 'disetujui'],
            ['room' => $rooms[2]->id, 'event_name' => 'Pelatihan Guru', 'date' => $base->copy()->addDays(5), 'start' => '13:00:00', 'end' => '15:00:00', 'status' => 'diajukan'],
            ['room' => $rooms[3]->id, 'event_name' => 'Latihan Praktikum', 'date' => $base->copy()->addDays(6), 'start' => '07:30:00', 'end' => '09:30:00', 'status' => 'disetujui'],
            ['room' => $rooms[5]->id, 'event_name' => 'Pembuatan Video', 'date' => $base->copy()->addDays(7), 'start' => '10:00:00', 'end' => '12:00:00', 'status' => 'diajukan'],
            ['room' => $rooms[4]->id, 'event_name' => 'Lomba Coding', 'date' => $base->copy()->addDays(8), 'start' => '08:00:00', 'end' => '11:00:00', 'status' => 'disetujui'],
            ['room' => $rooms[6]->id, 'event_name' => 'Acara Sekolah', 'date' => $base->copy()->addDays(10), 'start' => '09:00:00', 'end' => '12:00:00', 'status' => 'disetujui'],
            ['room' => $rooms[7]->id, 'event_name' => 'Koordinasi OSIS', 'date' => $base->copy()->addDays(11), 'start' => '15:00:00', 'end' => '17:00:00', 'status' => 'diajukan'],
            ['room' => $rooms[1]->id, 'event_name' => 'Konsultasi BK', 'date' => $base->copy()->addDays(12), 'start' => '11:00:00', 'end' => '12:00:00', 'status' => 'disetujui'],
            ['room' => $rooms[0]->id, 'event_name' => 'Try Out', 'date' => $base->copy()->addDays(15), 'start' => '13:00:00', 'end' => '15:00:00', 'status' => 'diajukan'],
        ];

        foreach ($events as $eventData) {
            Event::updateOrCreate([
                'room_id' => $eventData['room'],
                'event_name' => $eventData['event_name'],
                'date' => $eventData['date']->toDateString(),
            ], [
                'pic_id' => $guru->id,
                'start_time' => $eventData['start'],
                'end_time' => $eventData['end'],
                'status' => $eventData['status'],
                'approved_by' => $guru->id,
            ]);
        }
    }
}
