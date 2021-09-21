<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintOnMacAddressInPadlocks extends Migration
{
    public function up()
    {
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX upper_case_mac_address_padlocks
ON padlocks (UPPER(mac_address));
SQL
        );
    }

    public function down()
    {
        \DB::statement("DROP INDEX upper_case_mac_address_padlocks");
    }
}
