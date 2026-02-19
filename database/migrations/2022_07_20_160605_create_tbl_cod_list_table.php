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
        Schema::create('tbl_cod_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('order_id');
            $table->text('ordered_item')->nullable();
            $table->double('subtotal')->nullable();
            $table->integer('status')->default(0);
            $table->string('date_ordered')->nullable();
            $table->string('date_completed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cod_list');
    }
};
