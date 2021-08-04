<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveInsuranceDefaultValue extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table
                ->decimal("estimated_insurance", 8, 2)
                ->default(null)
                ->change();
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table
                ->decimal("estimated_insurance", 8, 2)
                ->default(0)
                ->change();
        });
    }
}
