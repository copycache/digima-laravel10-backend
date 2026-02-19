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
        Schema::create('tbl_eloading_product', function (Blueprint $table) {
            $table->id('eloading_product_id');
            $table->string('eloading_product_name');
            $table->string('eloading_product_code');
            $table->text('eloading_product_description')->nullable();
            $table->string('eloading_product_validity')->nullable();
            $table->text('eloading_product_guide')->nullable();
            $table->string('eloading_product_type')->default('ELOAD');
            $table->string('eloading_product_subscriber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_eloading_product');
    }
};
