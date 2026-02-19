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
        Schema::create('tbl_retailer', function (Blueprint $table) {
            $table->id('retailer_id');
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('dealer_slot_id');
            $table->dateTime('retailer_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_retailer');
    }
};
