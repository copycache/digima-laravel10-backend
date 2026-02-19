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
        Schema::create('tbl_eloading_settings', function (Blueprint $table) {
            $table->id('eloading_settings_id');
            $table->integer('eloading_additional_wallet_percentage')->default('10');
            $table->string('eloading_wallet_receiver')->default('LOAD WALLET');
            $table->timestamps();
            $table->integer('eloading_is_active')->default('0');
            $table->integer('eloading_discount_wallet_percentage')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_eloading_settings');
    }
};
