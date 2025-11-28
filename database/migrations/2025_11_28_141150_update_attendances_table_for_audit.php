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
        Schema::table('attendances', function (Blueprint $table) {
            // Kolom status kehadiran yang lebih spesifik
            // Opsi: Masuk, Izin Telat, WFH, Telat, Alpha, Libur, Sakit, Cuti
            $table->string('presence_status')->nullable()->after('status')->comment('Status kehadiran spesifik (Hadir, Sakit, Cuti, dll)');
            
            // Bukti foto dari Audit/Admin
            $table->string('audit_photo_path')->nullable()->after('photo_out_path');
            
            // Catatan tambahan dari audit (opsional)
            $table->text('audit_note')->nullable()->after('audit_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['presence_status', 'audit_photo_path', 'audit_note']);
        });
    }
};
