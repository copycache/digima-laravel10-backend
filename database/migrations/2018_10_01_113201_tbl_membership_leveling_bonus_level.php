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
        Schema::create('tbl_membership_leveling_bonus_level', function (Blueprint $table) {
            $table->integer('membership_level');
            $table->unsignedInteger('membership_id');
            $table->integer('membership_entry_id');
            $table->double('membership_leveling_bonus_income')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_membership_leveling_bonus_level');
    }
};
