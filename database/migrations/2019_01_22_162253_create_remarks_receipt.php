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
        Schema::table('tbl_orders', function (Blueprint $table) {
            $table->text('remarks')->nullable();
        });

        Schema::table('tbl_receipt', function (Blueprint $table) {
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_receipt', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('tbl_orders', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
