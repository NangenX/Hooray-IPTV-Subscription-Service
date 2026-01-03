<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('current_package_id')->nullable();
            $table->string('language_preference', 10)->default('en');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
