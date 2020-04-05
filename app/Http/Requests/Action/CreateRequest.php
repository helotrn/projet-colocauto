<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $loan = Loan::accessibleBy($user)->find($this->get('loan_id'));
        if ($loan) {
            return true;
        }

        return false;
    }

    public function rules() {
        $user = $this->user();
        $accessibleLoanIds = implode(',', Loan::accessibleBy($user)->pluck('id')->toArray());

        return [
            'type' => [
                'required',
                'in:incident,extension',
            ],
            'loan_id' => [
                "in:$accessibleLoanIds",
            ],
        ];
    }
}
