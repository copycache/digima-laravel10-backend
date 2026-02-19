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
        Schema::create('tbl_binary_points_settings', function (Blueprint $table) {
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('membership_entry_id');
            $table->double('membership_binary_points')->default(0);
            $table->double('max_slot_per_level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_binary_points_settings');
    }
};
