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
        Schema::create('tbl_flushout_log', function (Blueprint $table) {
            $table->id('flushout_log_id');
            $table->double('flushout_amount');
            $table->unsignedInteger('from_wallet_log_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_flushout_log');
    }
};
