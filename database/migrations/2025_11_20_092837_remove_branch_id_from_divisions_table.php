<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBranchIdFromDivisionsTable extends Migration
{
    public function up()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }

    public function down()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
        });
    }
}