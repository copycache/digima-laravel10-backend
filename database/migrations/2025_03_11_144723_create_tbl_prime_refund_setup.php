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
        Schema::create('tbl_prime_refund_setup', function (Blueprint $table) {
            $table->unsignedInteger('membership_id')->nullable();
            $table->unsignedInteger('membership_entry_id')->nullable();
            $table->double('prime_refund_points')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_prime_refund_setup');
    }
};
