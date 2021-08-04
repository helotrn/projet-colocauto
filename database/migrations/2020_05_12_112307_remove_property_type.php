<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePropertyType extends Migration
{
    public function up()
    {
        Schema::table("cars", function (Blueprint $table) {
            $table->dropColumn("ownership");
        });
    }

    public function down()
    {
        Schema::table("cars", function (Blueprint $table) {
            $table->enum("ownership", ["self", "rental"]);
        });
    }
}
