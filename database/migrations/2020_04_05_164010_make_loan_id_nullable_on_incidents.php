<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLoanIdNullableOnIncidents extends Migration
{
    public function up()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->unsignedBigInteger("loan_id")
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->unsignedBigInteger("loan_id")
                ->nullable(false)
                ->change();
        });
    }
}
