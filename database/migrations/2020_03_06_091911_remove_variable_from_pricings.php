<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveVariableFromPricings extends Migration
{
    public function up()
    {
        Schema::table("pricings", function (Blueprint $table) {
            $table->dropColumn("variable");
            $table
                ->string("object_type")
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("pricings", function (Blueprint $table) {
            $table->enum("variable", ["time", "distance"])->default("time");
            $table
                ->string("object_type")
                ->nullable(false)
                ->change();
        });
    }
}
