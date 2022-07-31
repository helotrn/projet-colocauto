<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;
use App\Models\PrePayment;

class DestroyRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        return $user->isAdmin() ||
            Loanable::where("owner_id", $user->owner->id)
                ->where("id", $this->route("id"))
                ->exists();
    }

    public function rules()
    {
        // Disallow archiving loanables when there are prepaid loans that are
        // not cancelled or completed.
        $prepaidLoansLoanableIds = Loan::prepaid()
            ->completed(false)
            ->canceled(false)
            ->pluck("loanable_id")
            ->join(",");
        return [
            "id" => ["not_in:$prepaidLoansLoanableIds"],
        ];
    }

    public function messages()
    {
        return [
            "id.not_in" => "Ce véhicule a des emprunts en cours.",
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            "id" => $this->route("id"),
        ]);
    }
}
