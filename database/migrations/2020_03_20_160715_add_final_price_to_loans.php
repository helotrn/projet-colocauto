<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalPriceToLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->decimal("final_price", 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("final_price");
        });
    }
}
