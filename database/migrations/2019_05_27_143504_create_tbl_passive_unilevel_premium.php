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
        Schema::create('tbl_passive_unilevel_premium', function (Blueprint $table) {
            $table->id('premium_id');
            $table->unsignedInteger('premium_membership_id');
            $table->integer('premium_upline')->default(0);
            $table->integer('premium_downline')->default(0);
            $table->double('premium_percentage')->default(0);
            $table->smallInteger('premium_is_enable')->default(0);
            $table->double('premium_earning_limit')->default(0);
            $table->smallInteger('premium_earning_cycle')->default(0);
            $table->dateTime('premium_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_passive_unilevel_premium');
    }
};
