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
        if (!Schema::hasTable('tbl_tin_logs')) {
            Schema::create('tbl_tin_logs', function (Blueprint $table) {
                $table->id('tin_logs_id');
                $table->unsignedInteger('user_id');
                $table->text('tin')->nullable();
                $table->dateTime('tin_date_change');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tin_logs');
    }
};
