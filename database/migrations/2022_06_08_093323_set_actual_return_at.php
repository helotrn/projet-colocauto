<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetActualReturnAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
          Test cases:
           - No extension
           - One accepted extension
           - One accepted extension, one in process
           - Two accepted extensions
           - No extension, early payment
           - Accepted extension, early payment
        */
        \DB::statement(
            <<<SQL
UPDATE loans as l SET actual_return_at =
  -- Work with timestamps directly, not durations.
  GREATEST(
    -- Don't allow return before departure
    loans.departure_at,
    -- Earliest between loan and payment accounting for extensions.
    LEAST(
      COALESCE(loan_payment.executed_at, '9999-12-31 00:00:00'),
      -- If extension exists, then it is necessarily longer than initial loan.
      loans.departure_at + COALESCE(extension_max_duration.max_duration, loans.duration_in_minutes) * interval '1 minute'
    )
  )

FROM "loans"

LEFT JOIN (
  SELECT
    max(new_duration) AS max_duration,
    loan_id
  FROM extensions
  WHERE status = 'completed'
  GROUP BY loan_id
) as "extension_max_duration" on "extension_max_duration"."loan_id" = "loans"."id"

LEFT JOIN (
  SELECT
    payments.executed_at,
    payments.loan_id
  FROM payments
  INNER JOIN loans l ON l.id = payments.loan_id
  WHERE payments.status = 'completed'
) as "loan_payment" on "loan_payment"."loan_id" = "loans"."id"

WHERE l.id = loans.id
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
        //
    }
}
