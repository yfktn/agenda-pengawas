<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penugasan_pengawas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('master_sekolah_id')->constrained('master_sekolahs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'master_sekolah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_pengawas');
    }
};
