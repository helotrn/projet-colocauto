<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsOnIncidentToIncidents extends Migration
{
    public function up()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table->text("comments_on_incident")->default("");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->text("comments_on_incident")
                ->default(null)
                ->change();
        });
    }

    public function down()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table->dropColumn("comments_on_incident");
        });
    }
}
