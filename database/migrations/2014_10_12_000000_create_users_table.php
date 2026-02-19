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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('type')->default('member');
            $table->string('social_id')->nullable();
            $table->string('registration_platform')->default('system');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->integer('position_id')->default(0);
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('contact')->nullable();
            $table->string('tin')->nullable();
            $table->text('crypt');
            $table->string('profile_picture')->default('../../../assets/front/img/boy.svg');
            $table->string('valid_id')->default('https://image.flaticon.com/icons/svg/71/71619.svg');
            $table->tinyInteger('verified')->default(0);
            $table->string('team_name')->nullable();
            $table->string('top_earner_status')->default('1');
            $table->integer('email_verified')->default(0);
            $table->string('front_id')->default('../../../assets/admin/img/noimage.png');
            $table->string('back_id')->default('../../../assets/admin/img/noimage.png');
            $table->string('selfie_id')->default('../../../assets/admin/img/noimage.png');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
