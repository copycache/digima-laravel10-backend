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
        Schema::create('tbl_address', function (Blueprint $table) {
            $table->id('address_id');
            $table->string('address_postal_code');
            $table->string('regCode');
            $table->string('provCode');
            $table->string('citymunCode');
            $table->string('brgyCode');
            $table->string('additional_info')->nullable();
            $table->tinyInteger('is_default')->default(0);
            $table->tinyInteger('archived')->default(0);
            $table->integer('user_id');
            $table->string('island_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_address');
    }
};
