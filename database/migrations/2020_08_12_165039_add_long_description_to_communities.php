<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLongDescriptionToCommunities extends Migration
{
    public function up()
    {
        Schema::table("communities", function (Blueprint $table) {
            $table->text("long_description")->default("");
        });
    }

    public function down()
    {
        Schema::table("communities", function (Blueprint $table) {
            $table->text("long_description")->default("");
        });
    }
}
