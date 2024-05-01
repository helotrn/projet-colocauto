<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;

class SearchRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $user->borrower ||
            $user->isAdmin() ||
            ($user->isCommunityAdmin() && Loanable::accessibleBy($user)->find($this->id));
    }

    public function rules()
    {
        $rules = [
            "departure_at" => ["date", "required"],
            "duration_in_minutes" => ["integer", "required", "min:30"],
            "estimated_distance" => ["integer", "required", "min:0"],
        ];

        return $rules;
    }
}
