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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->string('category'); // elektronik, perkantoran, kendaraan, lainnya
            $table->string('serial_number')->nullable();
            $table->date('received_date');
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'perbaikan']);
            $table->text('description')->nullable();
            $table->string('item_photo_path')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};
