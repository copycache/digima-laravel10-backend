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
        Schema::create('tbl_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('code')->nullable();
            $table->string('date_generate')->nullable();
            $table->string('date_used')->nullable();
            $table->string('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_verification_codes');
    }
};
