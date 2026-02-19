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
        Schema::create('tbl_audit_trail', function (Blueprint $table) {
            $table->id('audit_trail_id');
            $table->unsignedInteger('user_id');
            $table->string('action')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->dateTime('date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_audit_trail');
    }
};
