<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            ['role_name' => 'Guru', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Organisasi', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->whereIn('role_name', ['Guru', 'Organisasi'])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.role_id', 'roles.id');
            })
            ->delete();
    }
};
