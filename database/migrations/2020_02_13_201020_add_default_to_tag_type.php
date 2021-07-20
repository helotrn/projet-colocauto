<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class AddDefaultToTagType extends Migration
{
    public function __construct()
    {
        if (!Type::hasType("enum")) {
            Type::addType("enum", StringType::class);
        }
    }

    public function up()
    {
        Schema::table("tags", function (Blueprint $table) {
            $table
                ->enum("type", ["tag"])
                ->default("tag")
                ->change();
        });
    }

    public function down()
    {
        Schema::table("tags", function (Blueprint $table) {
            $table
                ->enum("type", ["tag"])
                ->default(null)
                ->change();
        });
    }
}
