<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExplicitedCanceledAtOnLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table
                ->dateTimeTz("canceled_at")
                ->nullable()
                ->default(null);
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("canceled_at");
        });
    }
}
