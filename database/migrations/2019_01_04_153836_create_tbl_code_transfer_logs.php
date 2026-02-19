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
        Schema::create('tbl_code_transfer_logs', function (Blueprint $table) {
            $table->id('code_transfer_log_id');
            $table->unsignedInteger('code_id');
            $table->unsignedInteger('from_slot');
            $table->unsignedInteger('to_slot');
            $table->unsignedInteger('original_slot');
            $table->dateTime('date_transfer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_code_transfer_logs');
    }
};
