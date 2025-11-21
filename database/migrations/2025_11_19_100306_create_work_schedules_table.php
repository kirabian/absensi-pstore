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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->onDelete('cascade');
            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->onDelete('cascade');
            $table->string('schedule_name');
            $table->time('check_in_start')->nullable();
            $table->time('check_in_end')->nullable();
            $table->time('check_out_start')->nullable();
            $table->time('check_out_end')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk performa
            $table->index(['branch_id', 'division_id']);
            $table->index('is_default');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
