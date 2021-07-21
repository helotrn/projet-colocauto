<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndicesToMaterializedViews extends Migration
{
    public function up()
    {
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX actions_index
ON actions (id, type);
SQL
        );

        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX loanables_index
ON loanables (id, type);
SQL
        );
    }

    public function down()
    {
        \DB::statement("DROP INDEX actions_index");
        \DB::statement("DROP INDEX loanables_index");
    }
}
