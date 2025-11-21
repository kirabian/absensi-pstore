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
    Schema::table('leave_requests', function (Blueprint $table) {
        // Menambahkan kolom waktu setelah end_date
        // Dibuat nullable karena kalau Izin Sakit, jam tidak dibutuhkan
        $table->time('start_time')->nullable()->after('end_date');
        $table->time('end_time')->nullable()->after('start_time');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('leave_requests', function (Blueprint $table) {
        $table->dropColumn(['start_time', 'end_time']);
    });
}
};
