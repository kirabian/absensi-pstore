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
        Schema::table('divisions', function (Blueprint $table) {
            // Hapus foreign key dulu (array syntax)
            $table->dropForeign(['branch_id']);
            // Baru hapus kolomnya
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Jika di-rollback, kembalikan kolomnya (Opsional)
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches');
        });
    }
};
