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
        Schema::create('tbl_slot_transfer', function (Blueprint $table) {
            $table->id('slot_transfer_id');
            $table->integer('owner_id');
            $table->integer('transferred_to');
            $table->integer('slot_id');
            $table->dateTime('date_transferred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_slot_transfer');
    }
};
