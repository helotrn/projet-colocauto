<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCargoToBikeTypes extends Migration
{
    public function up()
    {
        Schema::table("bikes", function (Blueprint $table) {
            $table->dropColumn("bike_type");
        });
        Schema::table("bikes", function (Blueprint $table) {
            $table
                ->enum("bike_type", [
                    "regular",
                    "cargo",
                    "electric",
                    "fixed_wheel",
                ])
                ->default("regular");
        });
    }

    public function down()
    {
        Schema::table("bikes", function (Blueprint $table) {
            $table->dropColumn("bike_type");
        });
        Schema::table("bikes", function (Blueprint $table) {
            $table->enum("bike_type", ["regular", "electric", "fixed_wheel"]);
        });
    }
}
