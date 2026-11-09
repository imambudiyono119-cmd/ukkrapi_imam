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
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade'); // FK -> rooms.id
            $table->foreignId('pic_id')->constrained('users')->onDelete('cascade'); // FK -> users.id (Pemohon/Penanggung jawab)
            $table->string('event_name'); // Biasa - Nama kegiatan
            $table->date('date'); // Biasa - Tanggal kegiatan
            $table->time('start_time'); // Biasa - Jam mulai
            $table->time('end_time'); // Biasa - Jam selesai
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan'); // ENUM
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // FK -> users.id (Waka/Admin)
            $table->text('move_reason')->nullable(); // Biasa (boleh NULL) - Alasan pemindahan ruangan oleh Waka
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
