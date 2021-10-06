<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class PersonalAccessClient extends Migration
{
    public function up()
    {
        Artisan::call("passport:client", [
            "--personal" => true,
            "--quiet" => true,
        ]);
    }

    public function down()
    {
    }
}
