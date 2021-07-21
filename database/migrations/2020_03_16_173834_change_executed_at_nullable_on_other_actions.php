<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class ChangeExecutedAtNullableOnOtherActions extends Migration
{
    public function __construct()
    {
        if (!Type::hasType("enum")) {
            Type::addType("enum", StringType::class);
        }
    }

    public function up()
    {
        foreach (
            ["takeovers", "handovers", "incidents", "extensions"]
            as $name
        ) {
            Schema::table($name, function (Blueprint $table) {
                $table
                    ->dateTimeTz("executed_at")
                    ->nullable()
                    ->change();
                $table
                    ->enum("status", ["in_process", "canceled", "completed"])
                    ->default("in_process")
                    ->change();
            });
        }
    }

    public function down()
    {
        foreach (
            ["takeovers", "handovers", "incidents", "extensions"]
            as $name
        ) {
            Schema::table($name, function (Blueprint $table) {
                $table
                    ->dateTimeTz("executed_at")
                    ->nullable(false)
                    ->change();
                $table
                    ->enum("status", ["in_process", "canceled", "completed"])
                    ->default(null)
                    ->change();
            });
        }
    }
}
