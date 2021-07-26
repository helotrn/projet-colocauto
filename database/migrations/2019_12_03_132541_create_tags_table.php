<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    public function up()
    {
        Schema::create("tags", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("name");
            $table->enum("type", ["tag"]);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("taggables", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("taggable_type")->nullable();
            $table
                ->integer("taggable_id")
                ->unsigned()
                ->nullable();

            $table->unsignedBigInteger("tag_id");

            $table
                ->foreign("tag_id")
                ->references("id")
                ->on("tags")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("taggables");
        Schema::dropIfExists("tags");
    }
}
