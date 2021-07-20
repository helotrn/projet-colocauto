<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBikesTable extends Migration
{
    public function up()
    {
        Schema::create("bikes", function (Blueprint $table) {
            $table->bigIncrements("id");

            //Loanable fields
            $table->string("name");
            $table->point("position");
            $table->text("location_description");
            $table->text("comments");
            $table->text("instructions");
            $table->text("availability_ics");
            $table->unsignedBigInteger("owner_id")->nullable();
            $table->unsignedBigInteger("community_id")->nullable();

            //Bike-specific fields
            $table->string("model");
            $table->enum("type", ["regular", "electric", "fixed_wheel"]);
            $table->enum("size", ["big", "medium", "small", "kid"]);

            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign("owner_id")
                ->references("id")
                ->on("owners")
                ->onDelete("cascade");
            $table
                ->foreign("community_id")
                ->references("id")
                ->on("communities")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("bikes");
    }
}
