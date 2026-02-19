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
        Schema::create('tbl_reverse_pass_up_settings', function (Blueprint $table) {
            $table->id('pass_up_settings_id');
            $table->integer('membership_id');
            $table->double('direct');
            $table->integer('direct_direction');
            $table->double('direct_amount');
            $table->double('pass_up');
            $table->integer('pass_up_direction');
            $table->double('pass_up_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_reverse_pass_up_settings');
    }
};
