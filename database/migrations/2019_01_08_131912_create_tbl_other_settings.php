<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_other_settings', function (Blueprint $table) {
            $table->id('other_settings_id');
            $table->string('key')->nullable();
            $table->string('name')->nullable();
            $table->double('value')->default(0);
        });

        DB::table('tbl_other_settings')->insert([
            ['key' => 'register_google', 'name' => 'Google Registration', 'value' => '1'],
            ['key' => 'register_facebook', 'name' => 'Facebook Registration', 'value' => '1'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_other_settings');
    }
};
