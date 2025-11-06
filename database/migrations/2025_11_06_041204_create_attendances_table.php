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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // User yang absen
            $table->timestamp('check_in_time');
            $table->string('status'); // 'pending_verification' atau 'verified'
            $table->string('photo_path')->nullable(); // Foto dari security

            // ID Security yang scan
            $table->foreignId('scanned_by_user_id')->nullable()->constrained('users');
            // ID Audit yang verifikasi
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
