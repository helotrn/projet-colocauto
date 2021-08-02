<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class ChangeValuesForIncidentType extends Migration
{
    public function __construct()
    {
        if (!Type::hasType("enum")) {
            Type::addType("enum", StringType::class);
        }
    }

    public function up()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table->dropColumn("incident_type");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->enum("incident_type", ["accident", "small_incident"])
                ->default("accident");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->enum("incident_type", ["accident", "small_incident"])
                ->default(null)
                ->change();
        });
    }

    public function down()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table->dropColumn("incident_type");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table->enum("incident_type", ["accident"])->default("accident");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->enum("incident_type", ["accident"])
                ->default(null)
                ->change();
        });
    }
}
