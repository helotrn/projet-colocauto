<?php

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() ||
            ($this->user()->isCommunityAdmin() && Community::accessibleBy($this->user())->find($this->route('community_id'));
    }

    public function rules()
    {
        $rules = [
            "email" => "string",
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            "email.required" => "L'adresse email est requise.",
        ];
    }
}
