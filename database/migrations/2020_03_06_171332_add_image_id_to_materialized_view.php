<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageIdToMaterializedView extends Migration
{
    public function up() {
        \DB::statement('DROP MATERIALIZED VIEW loanables');
        \DB::statement(<<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, created_at, updated_at, deleted_at, image_id) AS
    SELECT cars.id, 'car' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, cars.created_at, cars.updated_at, cars.deleted_at, images.id AS image_id
    FROM cars
    LEFT JOIN images ON images.imageable_id = cars.id AND images.imageable_type = 'App\Models\Car'
UNION
    SELECT bikes.id, 'bike' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, bikes.created_at, bikes.updated_at, bikes.deleted_at, images.id AS image_id
    FROM bikes
    LEFT JOIN images ON images.imageable_id = bikes.id AND images.imageable_type = 'App\Models\Bike'
UNION
    SELECT trailers.id, 'trailer' AS type, name, position, location_description, comments, instructions, availability_ics, owner_id, community_id, trailers.created_at, trailers.updated_at, trailers.deleted_at, images.id AS image_id
    FROM trailers
    LEFT JOIN images ON images.imageable_id = trailers.id AND images.imageable_type = 'App\Models\Trailer'
SQL
        );
    }

    public function down() {
        \DB::statement('DROP MATERIALIZED VIEW loanables');
        \DB::statement(<<<SQL
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
}
