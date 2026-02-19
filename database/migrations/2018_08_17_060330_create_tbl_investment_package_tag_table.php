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
        Schema::create('tbl_investment_package_tag', function (Blueprint $table) {
            $table->id('investment_package_tag_id');
            $table->string('investment_amount');
            $table->string('investment_date');
            $table->integer('investment_package_id');
            $table->integer('slot_id');
            $table->integer('user_id');
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_investment_package_tag');
    }
};
