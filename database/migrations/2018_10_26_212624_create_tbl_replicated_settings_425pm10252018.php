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
        Schema::create('tbl_replicated_settings', function (Blueprint $table) {
            $table->id('replicated_id');
            $table->string('replicated_name');
            $table->integer('replicated_sponsoring')->default(0);
            $table->tinyInteger('replicated_archived')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_replicated_settings');
    }
};
