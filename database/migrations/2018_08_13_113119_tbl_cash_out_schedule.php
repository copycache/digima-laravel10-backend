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
        Schema::create('tbl_cash_out_schedule', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->string('schedule_status');
            $table->dateTime('schedule_date_from');
            $table->dateTime('schedule_date_to');
            $table->double('total_payout_amount');
            $table->double('total_payout_charge');
            $table->double('total_payout_required');
            $table->dateTime('date_created');
            $table->double('total_payout_receivable');
            $table->integer('schedule_method_id')->default(0);
            $table->smallInteger('is_archived')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_out_schedule');
    }
};
