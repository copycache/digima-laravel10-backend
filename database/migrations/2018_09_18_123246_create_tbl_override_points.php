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
        if (!Schema::hasTable('tbl_override_points')) {
            Schema::create('tbl_override_points', function (Blueprint $table) {
                $table->id('override_points_id');
                $table->unsignedInteger('slot_id');
                $table->double('override_amount')->default(0);
                $table->tinyInteger('distributed')->default(0);
                $table->dateTime('override_points_date_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_override_points');
    }
};
