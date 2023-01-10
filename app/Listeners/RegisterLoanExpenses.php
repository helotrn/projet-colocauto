<?php

namespace App\Listeners;

use App\Events\LoanCompletedEvent;
use App\Models\Loan;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseTag;

class RegisterLoanExpenses
{
    public function handle(LoanCompletedEvent $event)
    {
        $loan = $event->loan;
        $distance = $loan->handover->mileage_end - $loan->takeover->mileage_beginning;

        $expense = new Expense;
        $expense->name = "{$loan->reason} ($distance km)";
        $expense->amount = $loan->actual_price;
        $expense->user_id = $loan->borrower->id;
        $expense->loanable_id = $loan->loanable->id;
        $expense->type = 'debit'; // user will pay for this loan
        $expense->executed_at = $loan->handover->executed_at;

        $tag = ExpenseTag::where('slug', 'loan');
        if( $tag ) $expense->tag()->associate($tag->first());

        $expense->save();

        if( $loan->handover->purchases_amount > 0 ) {
            $fuel = new Expense;
            $fuel->name = "";
            $fuel->amount = floatval($loan->handover->purchases_amount);
            $fuel->user_id = $loan->borrower->id;
            $fuel->loanable_id = $loan->loanable->id;
            $fuel->type = 'credit'; // user has already payed for this fuel
            $fuel->executed_at = $loan->handover->executed_at;

            $fuel_tag = ExpenseTag::where('slug', 'fuel');
            if( $fuel_tag ) $fuel->tag()->associate($fuel_tag->first());

            $fuel->save();
        }

        
    }
}
