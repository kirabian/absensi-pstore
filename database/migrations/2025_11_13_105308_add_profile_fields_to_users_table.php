<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('password');
            $table->string('ktp_photo_path')->nullable()->after('profile_photo_path');
            $table->date('hire_date')->nullable()->after('email');

            // --- PERBAIKAN URUTAN ADA DI SINI ---

            // 1. Buat 'linkedin' DULU
            $table->string('linkedin')->nullable()->after('email');

            // 2. BARU buat sisanya setelah 'linkedin'
            $table->string('whatsapp')->nullable()->after('linkedin');
            $table->string('instagram')->nullable()->after('linkedin');
            $table->string('tiktok')->nullable()->after('linkedin');
            $table->string('facebook')->nullable()->after('linkedin');
            // --- BATAS PERBAIKAN ---
        });
    }
    // (Function down() akan jadi kebalikannya, drop columns)

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Ini adalah kebalikan dari 'up'
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_path',
                'ktp_photo_path',
                'hire_date',
                'linkedin',
                'whatsapp',
                'instagram',
                'tiktok',
                'facebook',
            ]);
        });
    }
};
