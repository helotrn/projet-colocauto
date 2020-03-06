<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexOnMaterializedView extends Migration
{
    public function up() {
        \DB::statement(<<<SQL
CREATE UNIQUE INDEX loanables_index
ON loanables (id, type);
SQL
        );
    }

    public function down() {
        \DB::statement('DROP INDEX loanables_index');
    }
}
