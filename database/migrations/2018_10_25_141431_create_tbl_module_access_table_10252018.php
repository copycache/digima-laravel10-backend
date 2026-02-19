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
        Schema::create('tbl_module_access', function (Blueprint $table) {
            $table->id('module_access_id');
            $table->integer('position_id');
            $table->integer('module_access')->default(0);
            $table->unsignedInteger('module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_module_access');
    }
};
