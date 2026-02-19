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
        Schema::create('tbl_referral_voucher_settings', function (Blueprint $table) {
            $table->id();
            $table->string('membership_id')->nullable();
            $table->string('referrer_income')->nullable();
            $table->string('referee_income')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_referral_voucher_settings');
    }
};
