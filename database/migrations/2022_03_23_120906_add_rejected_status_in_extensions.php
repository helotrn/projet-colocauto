<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectedStatusInExtensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE extensions DROP CONSTRAINT extensions_status_check");

        $types = ['in_process', 'canceled', 'completed', 'rejected'];
        $result = join( ', ', array_map(function ($value){
            return sprintf("'%s'::character varying", $value);
        }, $types));

        DB::statement("ALTER TABLE extensions ADD CONSTRAINT extensions_status_check CHECK (status::text = ANY (ARRAY[$result]::text[]))");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE extensions DROP CONSTRAINT extensions_status_check");

        $types = ['in_process', 'canceled', 'completed'];
        $result = join( ', ', array_map(function ($value){
            return sprintf("'%s'::character varying", $value);
        }, $types));

        DB::statement("ALTER TABLE extensions ADD CONSTRAINT extensions_status_check CHECK (status::text = ANY (ARRAY[$result]::text[]))");
    }
}
