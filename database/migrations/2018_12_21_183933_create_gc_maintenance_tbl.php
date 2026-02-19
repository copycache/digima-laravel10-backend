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
        Schema::create('tbl_gc_maintenance', function (Blueprint $table) {
            $table->id('gc_maintenance_id');
            $table->integer('amount_required')->default(0);
            $table->integer('amount_deducted')->default(0);
            $table->integer('amount_given')->default(0);
            $table->string('status')->default('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_gc_maintenance');
    }
};
