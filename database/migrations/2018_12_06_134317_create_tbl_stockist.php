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
        Schema::create('tbl_stockist', function (Blueprint $table) {
            $table->id('stockist_id');
            $table->unsignedInteger('stockist_user_id');
            $table->unsignedInteger('stockist_branch_id');
            $table->unsignedInteger('stockist_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_stockist');
    }
};
