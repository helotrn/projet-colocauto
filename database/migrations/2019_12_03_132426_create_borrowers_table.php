<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorrowersTable extends Migration
{
    public function up()
    {
        Schema::create("borrowers", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("drivers_license_number")->nullable();
            $table->boolean("has_been_sued_last_ten_years")->default(false);
            $table->string("noke_id")->nullable();

            $table->unsignedBigInteger("user_id");

            $table->dateTimeTz("submitted_at")->nullable();
            $table->dateTimeTz("approved_at")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::dropIfExists("borrowers");
    }
}
