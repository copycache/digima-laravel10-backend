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
        Schema::create('tbl_payout_config', function (Blueprint $table) {
            $table->id('payout_config_id');
            $table->string('payout_config_type');
            $table->string('payout_config_name');
            $table->double('payout_minimum_encashment')->default(0);
            $table->tinyInteger('payout_config_enabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payout_config');
    }
};
