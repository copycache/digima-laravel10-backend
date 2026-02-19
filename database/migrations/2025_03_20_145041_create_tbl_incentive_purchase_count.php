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
        Schema::create('tbl_incentive_purchase_count', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->double('purchase_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_incentive_purchase_count');
    }
};
