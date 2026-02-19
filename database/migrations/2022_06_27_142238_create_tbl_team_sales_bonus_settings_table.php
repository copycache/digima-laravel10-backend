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
        Schema::create('tbl_team_sales_bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('item_id');
            $table->double('commission')->default(0);
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_team_sales_bonus_settings');
    }
};
