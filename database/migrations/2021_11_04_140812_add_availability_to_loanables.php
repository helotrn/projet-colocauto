<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailabilityToLoanables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("DROP MATERIALIZED VIEW loanables");
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, availability_mode, availability_json, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at) AS
    SELECT id, 'car' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM cars
UNION
    SELECT id, 'bike' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM bikes
UNION
    SELECT id, 'trailer' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM trailers
SQL
        );
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX loanables_index
ON loanables (id, type);
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("DROP MATERIALIZED VIEW loanables");
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at) AS
    SELECT id, 'car' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM cars
UNION
    SELECT id, 'bike' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM bikes
UNION
    SELECT id, 'trailer' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM trailers
SQL
        );
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX loanables_index
ON loanables (id, type);
SQL
        );
    }
}
