<?php

namespace App\Models\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can validate a loan
     *
     * @param User $user
     * @param Loan $loan
     * @return bool
     */
    public function validate(User $user, Loan $loan): bool
    {
        if ($loan->borrower->user->id === $user->id) {
            return true;
        }
        // TODO(#1084): Remove this check
        if (!$loan->loanable->owner) {
            return false;
        }
        return $loan->loanable->owner->user->id === $user->id;
    }
}
