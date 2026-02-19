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
        Schema::create('tbl_delivery_charge', function (Blueprint $table) {
            $table->id('method_id');
            $table->string('method_name');
            $table->double('method_charge');
            $table->smallInteger('enable')->default(1);
            $table->double('method_discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_delivery_charge');
    }
};
