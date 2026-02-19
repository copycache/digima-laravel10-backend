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
        Schema::create('tbl_branch', function (Blueprint $table) {
            $table->dateTime('branch_date_created');
            $table->id('branch_id');
            $table->string('branch_name');
            $table->string('branch_type');
            $table->string('branch_location');
            $table->tinyInteger('archived')->default(0);
            $table->unsignedInteger('stockist_level')->nullable();
            $table->tinyInteger('add_member')->default(0);
            $table->tinyInteger('create_slot')->default(0);
            $table->tinyInteger('custom_code')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_branch');
    }
};
