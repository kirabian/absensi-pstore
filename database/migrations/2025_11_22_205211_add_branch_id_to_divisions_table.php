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
            // Menambahkan kolom branch_id setelah id
            // Menggunakan nullable() agar tidak error jika sudah ada data sebelumnya
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained('branches')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('divisions', function (Blueprint $table) {
            // Hapus foreign key dan kolom jika rollback
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
