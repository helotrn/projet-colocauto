<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class HandoverRequest extends BaseRequest
{
    public function rules() {
        return [
            'fuel_end' => 'required:image',
            'mileage_end' => [
                'required:image',
                'integer'
            ],
        ];
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
