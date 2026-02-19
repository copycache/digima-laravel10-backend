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
        Schema::create('tbl_qoute_request', function (Blueprint $table) {
            $table->id('qoute_request_id');
            $table->string('qoute_request_name');
            $table->string('qoute_request_email');
            $table->string('qoute_request_phone');
            $table->text('qoute_request_message');
            $table->tinyInteger('qoute_request_status')->default(0);
            $table->integer('qoute_request_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_qoute_request');
    }
};
