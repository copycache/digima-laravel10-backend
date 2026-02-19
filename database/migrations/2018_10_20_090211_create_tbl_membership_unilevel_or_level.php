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
        Schema::create('tbl_membership_unilevel_or_level', function (Blueprint $table) {
            $table->integer('membership_level');
            $table->integer('membership_id');
            $table->integer('membership_entry_id');
            $table->double('membership_percentage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_membership_unilevel_or_level');
    }
};
