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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('photo_path');
            $table->string('handler_photo_path')->nullable()->after('handler_id');
            $table->string('handler_file_path')->nullable()->after('handler_photo_path');
            $table->text('handler_note')->nullable()->after('handler_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'handler_photo_path', 'handler_file_path', 'handler_note']);
        });
    }
};
