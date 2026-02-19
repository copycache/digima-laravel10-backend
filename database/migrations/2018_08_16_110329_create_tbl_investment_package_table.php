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
        Schema::create('tbl_investment_package', function (Blueprint $table) {
            $table->id('investment_package_id');
            $table->integer('investment_package_days_bond');
            $table->integer('investment_package_min_interest');
            $table->integer('investment_package_max_interest');
            $table->integer('investment_package_days_margin');
            $table->tinyInteger('archive')->default(0);
            $table->string('bind_membership')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_investment_package');
    }
};
