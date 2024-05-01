<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;
use App\Repositories\LoanRepository;

class IntentionRequest extends BaseRequest
{
    /*
       Request is authorized for
         - admins
         - owner of the loanable
    */
    public function authorize()
    {
        $loanRepository = new LoanRepository(new Loan());
        $loan = $loanRepository->find(
            $this->redirectAuth(BaseRequest::class),
            $this->route("loan_id")
        );

        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        // community admin can only change loans that are parts of its communities
        if ($user->isCommunityAdmin()) {
            if (Loan::accessibleBy($user)->find($loan->id)) {
                return true;
            }
        }

        if ($user->owner->id === $loan->loanable->owner->id) {
            return true;
        }

        return false;
    }
}
