<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsView extends Migration
{
    public function up()
    {
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW assets
(path, type, field, filename, foreign_id) AS
    SELECT
        images.path, 'image' AS type, images.field AS field,
        images.filename AS filename, images.id AS foreign_id
    FROM images
UNION
    SELECT
        files.path, 'file' AS type, files.field AS field,
        files.filename AS filename, files.id AS foreign_id
    FROM files
SQL
        );
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX assets_index
ON assets (path, type);
SQL
        );
    }

    public function down()
    {
        \DB::statement("DROP MATERIALIZED VIEW assets");
    }
}
