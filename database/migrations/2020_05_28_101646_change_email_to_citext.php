<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEmailToCitext extends Migration
{
    public function up()
    {
        \DB::statement("DELETE FROM users WHERE id IN (162,164);");
        \DB::statement("ALTER TABLE users ALTER COLUMN email TYPE citext;");
    }

    public function down()
    {
        // Noop
    }
}
