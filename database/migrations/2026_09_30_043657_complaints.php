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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade'); // FK -> rooms.id
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade'); // FK -> users.id (Pelapor)
            $table->text('description'); // Biasa - Uraian kerusakan
            $table->string('photo_path')->nullable(); // Biasa - Lokasi berkas foto
            $table->enum('status_repair', ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'])->default('Belum Diperbaiki'); // ENUM
            $table->foreignId('handler_id')->nullable()->constrained('users')->onDelete('set null'); // FK -> users.id (PIC yang menangani)
            $table->timestamp('reported_at')->nullable(); // Biasa (datetime)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
