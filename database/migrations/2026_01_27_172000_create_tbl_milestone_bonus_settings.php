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
        Schema::create('tbl_milestone_bonus_settings', function (Blueprint $table) {
            $table->id('milestone_settings_id');
            $table->string('milestone_type_limit')->default('pairs');
            $table->integer('milestone_cycle_limit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_milestone_bonus_settings');
    }
};
