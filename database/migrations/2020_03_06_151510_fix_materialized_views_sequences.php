<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMaterializedViewsSequences extends Migration
{
    public function up() {
        \DB::statement('CREATE SEQUENCE IF NOT EXISTS loanables_id_seq');
        foreach (['cars', 'bikes', 'trailers'] as $table) {
            \DB::statement("ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('loanables_id_seq'::regclass)");
        }
    }

    public function down() {
        foreach (['cars', 'bikes', 'trailers'] as $table) {
            \DB::statement("ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('{$table}_id_seq'::regclass)");
        }

        \DB::statement('DROP SEQUENCE loanables_id_seq');
    }
}
