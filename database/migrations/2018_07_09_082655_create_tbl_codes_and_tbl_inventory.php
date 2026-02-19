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
        Schema::create('tbl_inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->unsignedInteger('inventory_branch_id');
            $table->unsignedInteger('inventory_item_id')->nullable();
            $table->string('inventory_status')->nullable();
            $table->integer('inventory_quantity')->default(0);
            $table->integer('inventory_sold')->default(0);
            $table->integer('inventory_total')->default(0);
        });

        Schema::create('tbl_codes', function (Blueprint $table) {
            $table->id('code_id');
            $table->unsignedInteger('code_inventory_id');
            $table->string('code_activation');
            $table->string('code_pin');
            $table->unsignedInteger('code_sold_to')->nullable();
            $table->datetime('code_date_sold')->nullable();
            $table->datetime('code_date_used')->nullable();
            $table->unsignedInteger('code_used_by')->nullable();
            $table->tinyInteger('code_used')->default(0);
            $table->tinyInteger('code_sold')->default(0);
            $table->integer('code_slot_used')->nullable();
            $table->unsignedInteger('inventory_sold');
            $table->unsignedInteger('inventory_total');
            $table->tinyInteger('archived')->default(0);
            $table->unsignedInteger('kit_requirement')->nullable();
            $table->dateTime('date_packed')->nullable();
            $table->integer('dragonpay')->default(0);
            $table->integer('order_id')->nullable();
            $table->unsignedInteger('org_code_sold_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_codes');
        Schema::dropIfExists('tbl_inventory');
    }
};
