<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleAndFlagToCoowners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coowners', function (Blueprint $table) {
            $table->boolean("receive_notifications")->default(false);
            $table->string("title")->nullable();
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
            $table->dropColumn("receive_notifications");
            $table->dropColumn("title");
        });
    }
}
