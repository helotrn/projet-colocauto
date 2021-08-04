<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    public function up()
    {
        Schema::create("loans", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->dateTimeTz("departure_at");
            $table->unsignedInteger("duration_in_minutes");

            $table->unsignedBigInteger("borrower_id");

            $table->string("loanable_type")->nullable();
            $table->unsignedBigInteger("loanable_id")->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign("borrower_id")
                ->references("id")
                ->on("borrowers")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("loans");
    }
}
