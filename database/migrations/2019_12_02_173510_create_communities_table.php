<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunitiesTable extends Migration
{
    public function up()
    {
        Schema::create("communities", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("name");
            $table->text("description")->nullable();
            $table->polygon("area")->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists("communities");
    }
}
