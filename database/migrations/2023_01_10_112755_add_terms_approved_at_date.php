<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTermsApprovedAtDate extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dateTimeTz("conditions_accepted_at")->nullable();
        });

        DB::table("users")->update([
            "conditions_accepted_at" => DB::raw("users.created_at"),
        ]);
    }

    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("conditions_accepted_at");
        });
    }
}
