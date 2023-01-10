<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class HandoverRequest extends BaseRequest
{
    public function rules()
    {
        $loanId = $this->route("loan_id") ?: $this->get("loan_id");
        $loan = Loan::accessibleBy($this->user())->find($loanId);

        if ($loan->loanable->type === "car") {
            $loanable = $loan->getFullLoanable();
            $pricing = $loan->community->getPricingFor($loanable);

            if (!$pricing) {
                $price = 0;
            }

            $values = $pricing->evaluateRule(
                $this->get("mileage_end") - $loan->takeover->mileage_beginning,
                $loan->actual_duration_in_minutes,
                $loanable,
                $loan
            );
            $price = max(0, is_array($values) ? $values[0] : $values);

            return [
                "mileage_end" => ["required", "integer"],
                "purchases_amount" => ["numeric", "gte:0"],
            ];
        }

        return [];
    }

    /*
       Request is authorized for
         - admins
         - borrower involved in the loan
         - owner of the loanable
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

        if ($user->owner && $user->owner->id === $loan->loanable->owner->id) {
            return true;
        }

        return false;
    }

    public function attributes()
    {
        return [
            "purchases_amount" => "Total des dépenses",
        ];
    }
}
