<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSelfServiceToLoanables extends Migration
{
    public function up()
    {
        Schema::table("bikes", function (Blueprint $table) {
            $table->boolean("is_self_service")->default(false);
        });
        Schema::table("cars", function (Blueprint $table) {
            $table->boolean("is_self_service")->default(false);
        });
        Schema::table("trailers", function (Blueprint $table) {
            $table->boolean("is_self_service")->default(false);
        });

        \DB::statement("DROP MATERIALIZED VIEW loanables");
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, is_self_service, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at) AS
    SELECT id, 'car' AS type, name, position, location_description, comments, instructions, is_self_service, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM cars
UNION
    SELECT id, 'bike' AS type, name, position, location_description, comments, instructions, is_self_service, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM bikes
UNION
    SELECT id, 'trailer' AS type, name, position, location_description, comments, instructions, is_self_service, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM trailers
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
        \DB::statement("DROP MATERIALIZED VIEW loanables");
        \DB::statement(
            <<<SQL
CREATE MATERIALIZED VIEW loanables
(id, type, name, position, location_description, comments, instructions, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at) AS
    SELECT id, 'car' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM cars
UNION
    SELECT id, 'bike' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM bikes
UNION
    SELECT id, 'trailer' AS type, name, position, location_description, comments, instructions, availability_mode, availability_json, owner_id, community_id, share_with_parent_communities, created_at, updated_at, deleted_at FROM trailers
SQL
        );
        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX loanables_index
ON loanables (id, type);
SQL
        );

        Schema::table("bikes", function (Blueprint $table) {
            $table->dropColumn("is_self_service");
        });
        Schema::table("cars", function (Blueprint $table) {
            $table->dropColumn("is_self_service");
        });
        Schema::table("trailers", function (Blueprint $table) {
            $table->dropColumn("is_self_service");
        });
    }
}
