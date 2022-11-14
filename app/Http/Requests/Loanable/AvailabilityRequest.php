<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Rules\OrderRule;
use Illuminate\Validation\Rule;

class AvailabilityRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Is requested loanable accessible by user?
        if (Loanable::accessibleBy($user)->find($this->loanable_id)) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        $rules = [
            "start" => ["date", "required"],
            "end" => ["date", "required"],
            // At the moment, only getting unavailability is implemented.
            "responseMode" => ["required", Rule::in(["unavailable"])],
        ];

        return $rules;
    }
}
