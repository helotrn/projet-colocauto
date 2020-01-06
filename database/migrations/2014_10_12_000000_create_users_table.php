<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('last_name')->default('');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('google_id')->nullable();
            $table->text('description')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->default('');
            $table->string('postal_code')->default('');
            $table->string('phone')->default('');
            $table->boolean('is_smart_phone')->default(false);
            $table->string('other_phone')->default('');

            $table->date('approved_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('users');
    }
}
