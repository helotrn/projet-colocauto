<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class ChangePrepaymentExecutedAtNullable extends Migration
{
    public function __construct()
    {
        if (!Type::hasType("enum")) {
            Type::addType("enum", StringType::class);
        }
    }

    public function up()
    {
        Schema::table("pre_payments", function (Blueprint $table) {
            $table
                ->enum("status", ["in_process", "canceled", "completed"])
                ->default("in_process")
                ->change();
            $table
                ->dateTimeTz("executed_at")
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table("pre_payments", function (Blueprint $table) {
            $table
                ->enum("status", ["in_process", "canceled", "completed"])
                ->default(null)
                ->change();
            $table
                ->dateTimeTz("executed_at")
                ->nullable(false)
                ->change();
        });
    }
}
