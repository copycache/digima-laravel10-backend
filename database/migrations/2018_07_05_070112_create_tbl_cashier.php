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
        Schema::create('tbl_cashier', function (Blueprint $table) {
            $table->id('cashier_id');
            $table->unsignedInteger('cashier_branch_id');
            $table->unsignedInteger('cashier_user_id');
            $table->string('cashier_address')->nullable();
            $table->string('cashier_contact_number')->nullable();
            $table->string('cashier_position');
            $table->string('cashier_status');
            $table->dateTime('cashier_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashier');
    }
};
