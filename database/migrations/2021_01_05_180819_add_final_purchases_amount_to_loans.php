<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalPurchasesAmountToLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table
                ->decimal("final_purchases_amount", 8, 2)
                ->nullable()
                ->default(0);
        });

        Schema::table("loans", function (Blueprint $table) {
            $table
                ->decimal("final_purchases_amount", 8, 2)
                ->default(null)
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("final_purchases_amount");
        });
    }
}
