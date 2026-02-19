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
        Schema::create('tbl_overriding_commission_v2', function (Blueprint $table) {
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('membership_entry_id');
            $table->string('income')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_overriding_commission_v2');
    }
};
