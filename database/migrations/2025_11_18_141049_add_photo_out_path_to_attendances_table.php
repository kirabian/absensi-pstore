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
    Schema::table('attendances', function (Blueprint $table) {
        // Menambah kolom foto pulang setelah kolom photo_path
        $table->string('photo_out_path')->nullable()->after('photo_path');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   public function down()
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropColumn('photo_out_path');
    });
}
};
