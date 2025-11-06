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
        Schema::create('late_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // User yang lapor
            $table->text('message'); // Isi pesan (misal: "Macet di tol")
            $table->boolean('is_active')->default(true); // Status notifikasi
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
        Schema::dropIfExists('late_notifications');
    }
};
