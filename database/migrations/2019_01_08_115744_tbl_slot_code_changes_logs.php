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
        Schema::create('tbl_slot_code_change_logs', function (Blueprint $table) {
            $table->id('slot_code_changes_log_id');
            $table->unsignedInteger('user_id');
            $table->string('old_slot_code')->nullable();
            $table->string('new_slot_code')->nullable();
            $table->dateTime('date_change')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_slot_code_change_logs');
    }
};
