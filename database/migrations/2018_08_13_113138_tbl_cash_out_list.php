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
        Schema::create('tbl_cash_out_list', function (Blueprint $table) {
            $table->id('cash_out_id');
            $table->string('cash_out_name');
            $table->string('cash_out_slot_code');
            $table->unsignedInteger('cash_out_method_id');
            $table->string('cash_out_primary_info')->nullable();
            $table->text('cash_out_secondary_info')->nullable();
            $table->text('cash_out_optional_info')->nullable();
            $table->string('cash_out_email_address')->nullable();
            $table->string('cash_out_contact_number')->nullable();
            $table->string('cash_out_currency')->nullable();
            $table->double('cash_out_amount_requested');
            $table->double('cash_out_method_fee');
            $table->double('cash_out_method_tax');
            $table->double('cash_out_method_service_charge');
            $table->double('cash_out_net_payout');
            $table->double('cash_out_net_payout_actual');
            $table->longText('cash_out_method_message')->nullable();
            $table->string('cash_out_status')->default('pending');
            $table->dateTime('cash_out_date');
            $table->double('cash_out_original_amount_deducted');
            $table->unsignedInteger('schedule_id')->nullable();
            $table->text('cash_out_tin')->nullable();
            $table->double('cash_out_savings')->default(0);
            $table->text('cash_out_remarks')->nullable();
            $table->string('cash_out_type')->nullable();
            $table->string('gc_charge')->nullable();
            $table->string('product_charge')->nullable();
            $table->string('survey_charge')->nullable();
            $table->double('cash_limit')->default(0);
            $table->string('sender_name')->nullable();
            $table->string('control_number')->nullable();
            $table->text('receipt_thumbnail')->nullable();
            $table->string('txnid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_out_list');
    }
};
