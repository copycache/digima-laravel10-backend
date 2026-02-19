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
        Schema::create('tbl_monoline_points', function (Blueprint $table) {
            $table->integer('monoline_points');
            $table->integer('monoline_grad_stat');
            $table->double('excess_monoline_points');
            $table->unsignedInteger('slot_id'); // Removed before() as it's not applicable in Schema::create
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_monoline_points');
    }
};
