<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommunityUserTable extends Migration
{
    public function up() {
        Schema::create('community_user', function (Blueprint $table) {
            $table->bigInteger('community_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('role')->nullable();

            $table->foreign('community_id')
                ->references('id')->on('communities')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('community_user');
    }
}
