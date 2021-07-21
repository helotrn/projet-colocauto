<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailabilityFieldsToLoanables extends Migration
{
    public function up()
    {
        foreach (["cars", "bikes", "trailers"] as $type) {
            Schema::table($type, function (Blueprint $table) {
                $table
                    ->enum("availability_mode", ["always", "never"])
                    ->default("never");
                $table->text("availability_json")->default("[]");
                $table
                    ->text("availability_ics")
                    ->default("")
                    ->change();
            });
        }
    }

    public function down()
    {
        foreach (["cars", "bikes", "trailers"] as $type) {
            Schema::table($type, function (Blueprint $table) {
                $table->dropColumn("availability_mode");
                $table->dropColumn("availability_json");
                $table->text("availability_ics")->change();
            });
        }
    }
}
