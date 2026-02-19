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
        Schema::create('tbl_eloading_log', function (Blueprint $table) {
            $table->id('eloading_log_id');
            $table->string('eloading_log_rrn');
            $table->string('eloading_log_resp');
            $table->string('eloading_log_tid');
            $table->string('eloading_log_bal');
            $table->string('eloading_log_epin');
            $table->string('eloading_log_err');
            $table->string('eloading_log_phone');
            $table->string('eloading_log_amount');
            $table->timestamps();
            $table->unsignedInteger('wallet_log_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_eloading_log');
    }
};
