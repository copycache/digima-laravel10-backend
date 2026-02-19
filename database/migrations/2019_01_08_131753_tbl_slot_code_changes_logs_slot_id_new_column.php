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
        Schema::table('tbl_slot_code_change_logs', function (Blueprint $table) {
            $table->unsignedInteger('slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_slot_code_change_logs', function (Blueprint $table) {
            $table->dropColumn('slot_id');
        });
    }
};
