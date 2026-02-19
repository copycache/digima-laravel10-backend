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
        Schema::create('tbl_investment_package_logs', function (Blueprint $table) {
            $table->id('investment_package_logs_id');
            $table->string('investment_package_logs_date');
            $table->string('investment_package_logs_amount');
            $table->integer('investment_package_tag_id');
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_investment_package_logs');
    }
};
