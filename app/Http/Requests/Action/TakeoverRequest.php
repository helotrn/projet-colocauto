<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class TakeoverRequest extends BaseRequest
{
    public function rules() {
        $loanId = $this->route('loan_id') ?: $this->get('loan_id');
        $loan = Loan::accessibleBy($this->user())->find($loanId);

        if ($loan->loanable->type === 'car') {
            return [
                'mileage_beginning' => [
                    'required',
                    'integer'
                ],
            ];
        }

        return [];
    }

    public function authorize() {
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        $loan = Loan::find($this->get('loan_id'));

        if ($user->borrower && $user->borrower->id === $loan->borrower->id) {
            return true;
        }

        if ($user->owner && $user->owner->id === $loan->loanable->owner->id) {
            return true;
        }

        return false;
    }
}
