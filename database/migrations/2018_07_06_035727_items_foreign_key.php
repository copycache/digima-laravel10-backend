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
        // Foreign keys removed - columns already exist in original table creation
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
