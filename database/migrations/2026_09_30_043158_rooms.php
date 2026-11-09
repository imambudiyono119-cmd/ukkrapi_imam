<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('room_name')->unique(); // Biasa (Unique) - Nama ruangan
            $table->integer('capacity')->nullable(); // Biasa - Kapasitas
            $table->string('location')->nullable(); // Biasa - Lokasi/gedung
            $table->foreignId('pic_id')->nullable()->constrained('users')->onDelete('set null'); // FK -> users.id (PIC Ruang)
            $table->boolean('is_active')->default(true); // Biasa (boolean) - Ruangan aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
