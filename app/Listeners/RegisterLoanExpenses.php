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
        $expense->name = "$distance km parcourus";
        $expense->amount = $loan->actual_price;
        $expense->user_id = $loan->borrower->id;
        $expense->loanable_id = $loan->loanable->id;
        $expense->type = 'debit'; // user will pay for this loan
        $expense->executed_at = $loan->handover->executed_at;

        $tag = ExpenseTag::where('slug', 'loan');
        if( $tag ) $expense->tag()->associate($tag->first());

        $expense->save();
    }
}
