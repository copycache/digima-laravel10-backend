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
        Schema::create('tbl_team_sales_bonus_level', function (Blueprint $table) {
            $table->integer('membership_level');
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('membership_entry_id');
            $table->unsignedInteger('item_id');
            $table->double('team_sales_bonus')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_team_sales_bonus_level');
    }
};
