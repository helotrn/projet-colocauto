<?php

namespace App\Observers;

use Auth;
use App\Models\ExpenseChange;
use App\Models\Expense;
use App\Models\Loanable;
use App\Models\User;
use App\Models\Loan;

class ExpensesObserver
{
    public function saved($expense)
    {
        if ($expense->wasRecentlyCreated == false) {
            if($expense->changeDescription) {
              // save changes in the database when saved event is triggered once again
              ExpenseChange::create([
                  'user_id'     => Auth::user()->id,
                  'expense_id'  => $expense->id,
                  'description' => $expense->changeDescription,
              ]);
            } else {
              // Describe each change
              $changes = $expense->getChanges();
              if( array_key_exists("amount", $changes)
                && $changes["amount"] != $expense->getOriginal("amount")
              ) {
                $expense->changeDescription = $expense->getOriginal("amount") . " => " . $expense->getChanges()["amount"];
              }
              if( array_key_exists("loanable_id", $changes)
                && $changes["loanable_id"] != $expense->getOriginal("loanable_id")
              ) {
                $loanables = Loanable::all();
                if($expense->changeDescription) $expense->changeDescription .= ", ";
                $expense->changeDescription .= $loanables->find($expense->getOriginal("loanable_id"))->name . " => " . $loanables->find($expense->getChanges()["loanable_id"])->name;
              }
              if( array_key_exists("user_id", $changes)
                && $changes["user_id"] != $expense->getOriginal("user_id")
              ) {
                $users = User::all();
                if($expense->changeDescription) $expense->changeDescription .= ", ";
                $expense->changeDescription .= $users->find($expense->getOriginal("user_id"))->full_name . " => " . $users->find($expense->getChanges()["user_id"])->full_name;
              }
              if( array_key_exists("loan_id", $changes)
                && $changes["loan_id"] != $expense->getOriginal("loan_id")
              ) {
                $loans = Loan::all();
                if($expense->changeDescription) $expense->changeDescription .= ", ";
                $originalLoan = $loans->find($expense->getOriginal("loan_id"));
                $newLoan = $loans->find($expense->getChanges()["loan_id"]);
                $expense->changeDescription .= ($originalLoan ? $originalLoan->name : '') . " => " . ($newLoan ? $newLoan->name : '');
              }
            }
        }
    }
}
