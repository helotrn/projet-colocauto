<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetLoanStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Loan is in process by default.
        \DB::statement("UPDATE loans SET status = 'in_process'");

        // Loan is canceled if intention is canceled.
        // Self-service loans are accepted by definition, so don't cancel.
        \DB::statement(
            "UPDATE loans
             SET status = 'canceled'
             WHERE id IN (
                 SELECT loans.id
                 FROM loans
                 LEFT JOIN intentions ON intentions.loan_id = loans.id
                 INNER JOIN loanables ON loanables.id = loans.loanable_id
                 WHERE loans.status = 'in_process'
                 AND intentions.status = 'canceled'
                 AND NOT loanables.is_self_service
             )"
        );

        // Loan is canceled if takeover is canceled, but not contested.
        // Canceled takeovers for on-demand loanables are contested, not canceled.
        // Canceled takeovers for self-service loanables are canceled.
        \DB::statement(
            "UPDATE loans
             SET status = 'canceled'
             WHERE id IN (
                 SELECT loans.id
                 FROM loans
                 LEFT JOIN takeovers ON takeovers.loan_id = loans.id
                 INNER JOIN loanables ON loanables.id = loans.loanable_id
                 WHERE loans.status = 'in_process'
                 AND takeovers.status = 'canceled'
                 AND loanables.is_self_service
             )"
        );

        // Canceled if takeover is canceled and there is no handover.
        \DB::statement(
            "UPDATE loans
             SET status = 'canceled'
             WHERE id IN (
                 SELECT loans.id
                 FROM loans
                 LEFT JOIN takeovers ON takeovers.loan_id = loans.id
                 LEFT JOIN handovers ON handovers.loan_id = loans.id
                 WHERE loans.status = 'in_process'
                 AND takeovers.status = 'canceled'
                 AND handovers.status IS NULL
             )"
        );

        // Loan is canceled if pre-payment is canceled.
        \DB::statement(
            "UPDATE loans
             SET status = 'canceled'
             WHERE id IN (
                 SELECT loans.id
                 FROM loans
                 LEFT JOIN pre_payments ON pre_payments.loan_id = loans.id
                 WHERE pre_payments.status = 'canceled'
             )"
        );

        // Loan is complete if payment is complete.
        \DB::statement(
            "UPDATE loans
             SET status = 'completed'
             WHERE id IN (
                 SELECT loans.id
                 FROM loans
                 LEFT JOIN payments ON payments.loan_id = loans.id
                 WHERE payments.status = 'completed'
             )"
        );

        // Loan is canceled if loan has canceled_at
        \DB::statement(
            "UPDATE loans
             SET status = 'canceled'
             WHERE canceled_at IS NOT NULL"
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
