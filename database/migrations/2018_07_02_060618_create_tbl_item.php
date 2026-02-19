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
        Schema::create('tbl_item', function (Blueprint $table) {
            $table->integer('item_id');
            $table->string('item_sku');
            $table->string('item_thumbnail')->default('../../../assets/admin/img/noimage.png');
            $table->text('item_description');
            $table->text('item_inclusion_details')->nullable();
            $table->string('item_barcode');
            $table->double('item_price')->default(0);
            $table->double('item_charged')->default(0);
            $table->double('qty_charged')->default(0);
            $table->double('item_gc_price')->default(0);
            $table->string('item_type')->default('product');
            $table->string('item_category')->default('item');
            $table->string('item_sub_category')->nullable();
            $table->string('tag_as')->nullable();
            $table->double('item_points_incetives')->default(0);
            $table->integer('membership_id');
            $table->integer('slot_qty')->default(1);
            $table->double('inclusive_gc')->default(0);
            $table->dateTime('item_date_created');
            $table->tinyInteger('archived')->default(0);
            $table->string('first_name')->default('');
            $table->string('last_name')->default('');
            $table->string('contact')->default('');
            $table->unsignedInteger('country_id')->default(0);
            $table->unsignedInteger('item_inventory_id')->nullable();
            $table->double('item_pv')->default(0);
            $table->string('item_points_currency')->default('PHP');
            $table->string('code_user')->default('everyone');
            $table->tinyInteger('upgrade_own')->default(0);
            $table->string('item_availability')->default('all')->nullable();
            $table->double('cashback_points')->default(0);
            $table->double('cashback_wallet')->default(0);
            $table->double('item_vortex_token')->default(0);
            $table->smallInteger('is_kit_upgrade')->default(0);
            $table->integer('bind_membership_id')->default(0);
            $table->string('product_id')->nullable();
            $table->double('direct_cashback')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_item');
    }
};
