<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleAccountsTable extends Migration
{
    public function up() {
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('provider_user_id');
            $table->string('provider');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('google_accounts');
    }
}
