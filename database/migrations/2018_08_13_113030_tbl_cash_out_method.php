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
        Schema::create('tbl_cash_out_method', function (Blueprint $table) {
            $table->id('cash_out_method_id');
            $table->string('cash_out_method_category');
            $table->string('cash_out_method_name');
            $table->longText('cash_out_method_thumbnail');
            $table->double('minimum_payout');
            $table->string('cash_out_method_currency');
            $table->double('cash_out_method_method_fee');
            $table->double('cash_out_method_withholding_tax');
            $table->string('is_archived')->default(0);
            $table->double('cash_out_method_service_charge');
            $table->string('cash_out_method_charge_to')->default('exclusive');
            $table->integer('initial_payout')->default(0);
            $table->double('savings_percentage')->default(0);
            $table->string('cash_out_method_service_charge_type')->nullable();
            $table->integer('gc_charge')->default(0);
            $table->integer('product_charge')->default(0);
            $table->string('survey_charge')->nullable();
            $table->double('cash_limit')->default(0);
            $table->string('cash_out_proc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_out_method');
    }
};
