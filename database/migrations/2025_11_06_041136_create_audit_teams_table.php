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
        Schema::create('audit_teams', function (Blueprint $table) {
            $table->id();
            // ID dari user dengan role 'audit'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // ID dari divisi yang diawasi
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');

            // Tidak perlu timestamps untuk tabel pivot ini
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_teams');
    }
};
