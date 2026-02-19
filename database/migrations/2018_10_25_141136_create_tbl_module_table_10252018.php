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
        Schema::create('tbl_module', function (Blueprint $table) {
            $table->id('module_id');
            $table->string('module_name');
            $table->string('module_alias');
            $table->string('module_type');
            $table->integer('module_is_enable')->default(0);
            $table->integer('slot_is_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_module');
    }
};
