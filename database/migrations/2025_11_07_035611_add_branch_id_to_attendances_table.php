<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Ini akan mempermudah filter laporan per cabang
            $table->foreignId('branch_id')->nullable()->constrained('branches')->after('user_id');
        });
    }
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
