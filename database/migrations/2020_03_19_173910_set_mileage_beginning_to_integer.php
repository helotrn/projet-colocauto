<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetMileageBeginningToInteger extends Migration
{
    public function up()
    {
        Schema::table("takeovers", function (Blueprint $table) {
            $table->dropColumn("mileage_beginning");
        });

        Schema::table("takeovers", function (Blueprint $table) {
            $table->unsignedInteger("mileage_beginning")->nullable();
        });
    }

    public function down()
    {
        Schema::table("takeovers", function (Blueprint $table) {
            $table
                ->string("mileage_beginning")
                ->nullable()
                ->change();
        });
    }
}
