<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountTypeToBillItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // the type will determine whether the bill item is a debit or a credit :
        // - debit: amount is paid by the user
        // - credit: amount is added to the user balance
        Schema::table("bill_items", function (Blueprint $table) {
            $table
                ->enum("amount_type", ["debit", "credit"])
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("bill_items", function (Blueprint $table) {
            $table->dropColumn("amount_type");
        });
    }
}
