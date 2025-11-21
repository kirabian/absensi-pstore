<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek jika tabel sudah ada, drop dulu biar bersih (HATI-HATI DATA HILANG)
        Schema::dropIfExists('leave_requests');

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sakit', 'izin', 'telat']);
            $table->date('start_date');               // Tanggal Mulai
            $table->date('end_date')->nullable();     // Sampai Tanggal (Sakit)
            $table->time('start_time')->nullable();   // Jam Datang (Telat)
            $table->text('reason');
            $table->string('file_proof')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
};