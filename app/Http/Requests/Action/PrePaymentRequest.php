<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class PrePaymentRequest extends BaseRequest
{
    /*
       Request is authorized for
         - admins
         - borrower involved in the loan
    */
    public function authorize()
    {
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        $loan = Loan::find($this->get("loan_id"));
        if ($user->borrower && $user->borrower->id === $loan->borrower->id) {
            return true;
        }

        return false;
    }
}
