<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;

class SearchRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $user->borrower || $user->isAdmin();
    }

    public function rules()
    {
        $rules = [
            "departure_at" => ["date", "required"],
            "duration_in_minutes" => ["integer", "required", "min:15"],
            "community_id" => ["integer", "filled"],
        ];

        return $rules;
    }
}
