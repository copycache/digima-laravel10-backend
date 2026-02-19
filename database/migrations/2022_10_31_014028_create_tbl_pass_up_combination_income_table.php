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
        Schema::create('tbl_pass_up_combination_income', function (Blueprint $table) {
            $table->integer('membership_id')->default(0);
            $table->integer('membership_entry_id')->default(0);
            $table->double('pass_up_income')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pass_up_combination_income');
    }
};
