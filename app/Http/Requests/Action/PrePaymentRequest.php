<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;
use App\Repositories\LoanRepository;

class PrePaymentRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->borrower->id === $loan->borrower->id) {
            return true;
        }

        return false;
    }
}
