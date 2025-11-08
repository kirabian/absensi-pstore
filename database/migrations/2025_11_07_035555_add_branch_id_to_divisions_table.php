<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('divisions', function (Blueprint $table) {
            // Divisi (Tim) sekarang terikat ke satu Cabang
            $table->foreignId('branch_id')->nullable()->constrained('branches')->after('id');
        });
    }
    public function down()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
