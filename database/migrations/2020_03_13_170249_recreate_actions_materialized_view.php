<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateActionsMaterializedView extends Migration
{
    public function up()
    {
        \DB::statement("DROP MATERIALIZED VIEW actions");

        \DB::statement(
            <<<SQL
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
    SELECT id, 'extension' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM extensions
UNION
    SELECT id, 'pre_payment' AS type, executed_at, status, loan_id, created_at, updated_at, deleted_at FROM pre_payments;
SQL
        );

        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX actions_index
ON actions (id, type);
SQL
        );
    }

    public function down()
    {
        \DB::statement("DROP MATERIALIZED VIEW actions");

        \DB::statement(
            <<<SQL
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

        \DB::statement(
            <<<SQL
CREATE UNIQUE INDEX actions_index
ON actions (id, type);
SQL
        );
    }
}
