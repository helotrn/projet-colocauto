<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccoutBalanceToUsers extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->decimal("balance", 8, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("balance");
        });
    }
}
