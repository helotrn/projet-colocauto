<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeHandoverValueNullable extends Migration
{
    public function up()
    {
        Schema::table("handovers", function (Blueprint $table) {
            $table->dropColumn("mileage_end");
        });

        Schema::table("handovers", function (Blueprint $table) {
            $table->unsignedInteger("mileage_end")->nullable();
            $table
                ->string("fuel_end")
                ->nullable()
                ->change();
            $table
                ->decimal("purchases_amount", 8, 2)
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("handovers", function (Blueprint $table) {
            $table
                ->string("mileage_end")
                ->nullable(false)
                ->change();
            $table
                ->string("fuel_end")
                ->nullable(false)
                ->change();
            $table
                ->decimal("purchases_amount", 8, 2)
                ->nullable(false)
                ->change();
        });
    }
}
