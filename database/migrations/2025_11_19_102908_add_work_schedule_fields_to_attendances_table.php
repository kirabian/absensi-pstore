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
        Schema::table('attendances', function (Blueprint $table) {
            // Tambahkan foreign key untuk work_schedule_id
            $table->foreignId('work_schedule_id')
                  ->nullable()
                  ->after('branch_id')
                  ->constrained('work_schedules')
                  ->onDelete('set null');
            
            // Tambahkan kolom untuk tracking terlambat
            $table->boolean('is_late_checkin')
                  ->default(false)
                  ->after('work_schedule_id');
            
            // Tambahkan kolom untuk tracking pulang cepat
            $table->boolean('is_early_checkout')
                  ->default(false)
                  ->after('is_late_checkin');
            
            // Tambahkan kolom untuk jenis absensi
            $table->enum('attendance_type', ['scan', 'self', 'manual'])
                  ->default('scan')
                  ->after('is_early_checkout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Hapus foreign key constraint dulu
            $table->dropForeign(['work_schedule_id']);
            
            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'work_schedule_id',
                'is_late_checkin', 
                'is_early_checkout',
                'attendance_type'
            ]);
        });
    }
};