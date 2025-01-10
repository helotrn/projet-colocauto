<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum("location", ["front", "back", "right", "left", "inside"]);
            $table->boolean("scratch")->default(false);
            $table->boolean("bumps")->default(false);
            $table->boolean("stain")->default(false);
            $table->text("details")->nullable();
            $table->timestamps();
            $table->unsignedBigInteger("loanable_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
