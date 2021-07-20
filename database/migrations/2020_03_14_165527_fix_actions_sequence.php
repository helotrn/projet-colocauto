<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixActionsSequence extends Migration
{
    public function up()
    {
        \DB::statement("CREATE SEQUENCE IF NOT EXISTS actions_id_seq");

        foreach (
            [
                "payments",
                "takeovers",
                "handovers",
                "incidents",
                "intentions",
                "extensions",
                "pre_payments",
            ]
            as $table
        ) {
            \DB::statement(
                "ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('actions_id_seq'::regclass)"
            );
        }
    }

    public function down()
    {
        foreach (
            [
                "payments",
                "takeovers",
                "handovers",
                "incidents",
                "intentions",
                "extensions",
                "pre_payments",
            ]
            as $table
        ) {
            \DB::statement(
                "ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('{$table}_id_seq'::regclass)"
            );
        }

        \DB::statement("DROP SEQUENCE actions_id_seq");
    }
}
