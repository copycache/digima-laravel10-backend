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
        Schema::create('tbl_cash_in_method', function (Blueprint $table) {
            $table->id('cash_in_method_id');
            $table->string('cash_in_method_category');
            $table->string('cash_in_method_name');
            $table->longText('cash_in_method_thumbnail');
            $table->string('cash_in_method_currency');
            $table->double('cash_in_method_charge_fixed')->default(0);
            $table->double('cash_in_method_charge_percentage')->default(0);
            $table->integer('is_archived')->default(0);
            $table->text('primary_info')->nullable();
            $table->text('secondary_info')->nullable();
            $table->text('optional_info')->nullable();
            $table->double('cash_in_method_minimum_amount')->default(500);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_in_method');
    }
};
