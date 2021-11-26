<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOptInNewsletterFromUsers extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("opt_in_newsletter");
        });
    }

    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->boolean("opt_in_newsletter")->default(false);
        });
    }
}
