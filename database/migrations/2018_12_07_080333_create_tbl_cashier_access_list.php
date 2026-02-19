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
        Schema::create('tbl_cashier_access', function (Blueprint $table) {
            $table->id('cashier_access_id');
            $table->unsignedInteger('cashier_access_branch');
            $table->string('cashier_type')->nullable();
            $table->tinyInteger('add_member')->default(0);
            $table->tinyInteger('create_slot')->default(0);
            $table->tinyInteger('overall_discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashier_access');
    }
};
