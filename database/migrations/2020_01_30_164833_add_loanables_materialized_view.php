<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoanablesMaterializedView extends Migration
{
    public function up()
    {
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, created_at, updated_at, deleted_at) AS
    SELECT id, 'car' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, created_at, updated_at, deleted_at FROM cars
UNION
    SELECT id, 'bike' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, created_at, updated_at, deleted_at FROM bikes
UNION
    SELECT id, 'trailer' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, created_at, updated_at, deleted_at FROM trailers
SQL
        );
    }

    public function down()
    {
        \DB::statement("DROP MATERIALIZED VIEW loanables");
    }
}
