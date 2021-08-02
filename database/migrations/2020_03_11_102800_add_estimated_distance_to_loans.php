<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimatedDistanceToLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->unsignedInteger("estimated_distance");
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("estimated_distance");
        });
    }
}
