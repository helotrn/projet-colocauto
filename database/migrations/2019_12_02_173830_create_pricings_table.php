<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingsTable extends Migration
{
    public function up()
    {
        Schema::create("pricings", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("name");
            $table->string("object_type");
            $table->enum("variable", ["time", "distance"]);
            $table->text("rule");
            $table->unsignedBigInteger("community_id");

            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign("community_id")
                ->references("id")
                ->on("communities")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("pricings");
    }
}
