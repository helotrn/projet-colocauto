<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLoanableTypeOnLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("loanable_type");
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->string("loanable_type")->nullable();
        });
    }
}
