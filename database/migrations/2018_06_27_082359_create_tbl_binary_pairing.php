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
        Schema::create('tbl_binary_pairing', function (Blueprint $table) {
            $table->id('binary_pairing_id');
            $table->double('binary_pairing_left')->default(0);
            $table->double('binary_pairing_right')->default(0);
            $table->double('binary_pairing_bonus')->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->unsignedInteger('binary_pairing_membership')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_binary_pairing');
    }
};
