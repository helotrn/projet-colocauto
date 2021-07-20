<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsersNameDefault extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table
                ->string("name")
                ->default("")
                ->change();
        });
    }

    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string("name")->change();
        });
    }
}
