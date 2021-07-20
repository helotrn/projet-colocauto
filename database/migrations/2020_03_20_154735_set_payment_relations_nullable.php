<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetPaymentRelationsNullable extends Migration
{
    public function up()
    {
        Schema::table("payments", function (Blueprint $table) {
            $table
                ->unsignedBigInteger("bill_item_id")
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("payments", function (Blueprint $table) {
            $table
                ->unsignedBigInteger("bill_item_id")
                ->nullable(false)
                ->change();
        });
    }
}
