<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPadlockNameToPadlocks extends Migration
{
    public function up()
    {
        Schema::table("padlocks", function (Blueprint $table) {
            $table->string("name");
            $table->string("external_id");
            $table
                ->string("loanable_type")
                ->nullable()
                ->change();
            $table
                ->unsignedBigInteger("loanable_id")
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("padlocks", function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("external_id");
            $table
                ->string("loanable_type")
                ->nullable(false)
                ->change();
            $table
                ->unsignedBigInteger("loanable_id")
                ->nullable(false)
                ->change();
        });
    }
}
