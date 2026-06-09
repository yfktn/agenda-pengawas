<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('sekolah_id');
        });

        Schema::table('penugasan_pengawas', function (Blueprint $table) {
            $table->unique('master_sekolah_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['sekolah_id']);
        });

        Schema::table('penugasan_pengawas', function (Blueprint $table) {
            $table->dropUnique(['master_sekolah_id']);
        });
    }
};
