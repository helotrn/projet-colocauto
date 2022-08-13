<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddHasNotBeenSuedToBorrowers extends Migration
{
    public function up()
    {
        Schema::table("borrowers", function (Blueprint $table) {
            $table->boolean("has_not_been_sued_last_ten_years")->default(false);
        });

        \DB::query("UPDATE borrowers SET has_not_been_sued_last_ten_years = NOT has_been_sued_last_ten_years");

    }

    public function down()
    {
        Schema::table("borrowers", function (Blueprint $table) {
            $table->dropColumn("has_not_been_sued_last_ten_years");
        });
    }
}
