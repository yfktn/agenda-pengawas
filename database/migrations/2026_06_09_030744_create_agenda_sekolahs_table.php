<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_sekolahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('master_sekolah_id')->constrained('master_sekolahs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['agenda_id', 'master_sekolah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_sekolahs');
    }
};
