<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Waka/Admin' => Role::where('role_name', 'Waka/Admin')->firstOrFail(),
            'PIC Ruang' => Role::where('role_name', 'PIC Ruang')->firstOrFail(),
            'Guru' => Role::where('role_name', 'Guru')->firstOrFail(),
            'Organisasi' => Role::where('role_name', 'Organisasi')->firstOrFail(),
        ];

        $users = [
            ['username' => 'waka', 'fullname' => 'Waka Sekolah', 'phone_number' => '6281234567890', 'role_name' => 'Waka/Admin'],
            ['username' => 'pic1', 'fullname' => 'PIC 1 (Ruang Teori & Kelas)', 'phone_number' => '6285550001111', 'role_name' => 'PIC Ruang'],
            ['username' => 'pic2', 'fullname' => 'PIC 2 (Fasilitas Selain Lab & Kelas)', 'phone_number' => '6285550002222', 'role_name' => 'PIC Ruang'],
            ['username' => 'pic3', 'fullname' => 'Udin Frimawan', 'phone_number' => '6285550003333', 'role_name' => 'PIC Ruang'],
            ['username' => 'udinfrimawan', 'fullname' => 'Udin Frimawan', 'phone_number' => '6285550003333', 'role_name' => 'PIC Ruang'],
            ['username' => 'guru1', 'fullname' => 'Guru Satu', 'phone_number' => '6287770001111', 'role_name' => 'Guru'],
            ['username' => 'guru2', 'fullname' => 'Guru Dua', 'phone_number' => '6287770002222', 'role_name' => 'Guru'],
            ['username' => 'osis', 'fullname' => 'Organisasi Siswa', 'phone_number' => '6287780003333', 'role_name' => 'Organisasi'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'password' => Hash::make('password123'),
                    'fullname' => $userData['fullname'],
                    'phone_number' => $userData['phone_number'],
                    'role_id' => $roles[$userData['role_name']]->id,
                ]
            );
        }
    }
}
