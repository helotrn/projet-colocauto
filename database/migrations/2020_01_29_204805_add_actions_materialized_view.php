<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActionsMaterializedView extends Migration
{
    public function up() {
        \DB::statement(<<<SQL
CREATE MATERIALIZED VIEW actions
(id, type, executed_at, status, loan_id, created_at, updated_at, deleted_at) AS
    SELECT id, 'payment' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM payments
UNION
    SELECT id, 'takeover' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM takeovers
UNION
    SELECT id, 'handover' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM handovers
UNION
    SELECT id, 'incident' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM incidents
UNION
    SELECT id, 'intention' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM intentions
UNION
    SELECT id, 'extension' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM extensions;
SQL
        );
    }

    public function down() {
        \DB::statement('DROP MATERIALIZED VIEW actions');
    }
}
