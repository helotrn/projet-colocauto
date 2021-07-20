<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePadlocksTable extends Migration
{
    public function up()
    {
        Schema::create("padlocks", function (Blueprint $table) {
            $table->bigIncrements("id");

            $table->string("mac_address");
            $table->string("loanable_type");
            $table->unsignedBigInteger("loanable_id");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists("padlocks");
    }
}
