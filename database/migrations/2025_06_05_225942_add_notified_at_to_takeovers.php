<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotifiedAtToTakeovers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->dateTimeTz("to_complete_notified_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->dropColumn("to_complete_notified_at");
        });
    }
}
