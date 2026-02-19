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
        Schema::create('tbl_membership_upgrade_logs', function (Blueprint $table) {
            $table->id('membership_upgrade_log_id');
            $table->unsignedInteger('slot_id')->nullable();
            $table->unsignedInteger('old_membership_id')->nullable();
            $table->unsignedInteger('new_membership_id')->nullable();
            $table->dateTime('upgraded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_membership_upgrade_logs');
    }
};
