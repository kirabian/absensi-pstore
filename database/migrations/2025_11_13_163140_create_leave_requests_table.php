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
    public function up(): void
{
    Schema::create('leave_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->enum('type', ['sakit', 'telat', 'cuti', 'libur_mingguan']);
        $table->date('start_date');
        $table->date('end_date');
        $table->text('reason');
        $table->string('file_proof')->nullable();
        $table->enum('status', ['pending', 'approved_leader', 'approved_audit', 'approved_admin', 'rejected'])->default('pending');
        $table->text('rejection_reason')->nullable();
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
        Schema::dropIfExists('leave_requests');
    }
};
