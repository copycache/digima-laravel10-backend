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
        Schema::create('tbl_lockdown_logs', function (Blueprint $table) {
            $table->id('lock_down_id');
            $table->unsignedInteger('slot_id');
            $table->string('plan')->nullable();
            $table->string('entry')->nullable();
            $table->string('type')->nullable();
            $table->string('details')->nullable();
            $table->integer('currency_id')->default(0);
            $table->integer('level')->default(0);
            $table->unsignedInteger('item_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('cause_id')->nullable();
            $table->double('amount')->nullable();
            $table->double('override')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_lockdown_logs');
    }
};
