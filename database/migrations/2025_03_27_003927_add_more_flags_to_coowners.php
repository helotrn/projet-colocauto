<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFlagsToCoowners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coowners', function (Blueprint $table) {
            $table->boolean("pays_loan_price")->default(true);
            $table->boolean("pays_provisions")->default(true);
            $table->boolean("pays_owner")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coowners', function (Blueprint $table) {
            $table->dropColumn("pays_loan_price");
            $table->dropColumn("pays_provisions");
            $table->dropColumn("pays_owner");
        });
    }
}
