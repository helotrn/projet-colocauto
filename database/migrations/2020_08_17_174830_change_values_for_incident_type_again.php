<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class ChangeValuesForIncidentTypeAgain extends Migration
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
            $table->renameColumn("incident_type", "incident_type_tmp");
        });

        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->enum("incident_type", [
                    "accident",
                    "small_incident",
                    "general",
                ])
                ->default("accident");
        });
        Schema::table("incidents", function (Blueprint $table) {
            $table
                ->enum("incident_type", [
                    "accident",
                    "small_incident",
                    "general",
                ])
                ->default(null)
                ->change();
        });

        \DB::query("UPDATE incidents SET incident_type = incident_type_tmp");

        Schema::table("incidents", function (Blueprint $table) {
            $table->dropColumn("incident_type_tmp");
        });
    }

    public function down()
    {
        Schema::table("incidents", function (Blueprint $table) {
            $table->renameColumn("incident_type", "incident_type_tmp");
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

        \DB::query(
            "UPDATE incidents SET incident_type = incident_type_tmp " .
                "WHERE incident_type_tmp != 'general'"
        );

        Schema::table("incidents", function (Blueprint $table) {
            $table->dropColumn("incident_type_tmp");
        });
    }
}
