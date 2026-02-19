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
        Schema::create('tbl_adjust_wallet_log', function (Blueprint $table) {
            $table->id('adjust_wallet_id');
            $table->unsignedInteger('slot_id');
            $table->string('adjusted_detail')->nullable();
            $table->double('adjusted_amount')->default(0);
            $table->dateTime('date_created')->nullable();
            $table->string('adjusted_currency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_adjust_wallet_log');
    }
};
