<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntentionsTable extends Migration
{
    public function up()
    {
        Schema::create("intentions", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->dateTimeTz("executed_at");
            $table->enum("status", ["in_process", "canceled", "completed"]);
            $table->unsignedBigInteger("loan_id");

            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign("loan_id")
                ->references("id")
                ->on("loans")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("intentions");
    }
}
