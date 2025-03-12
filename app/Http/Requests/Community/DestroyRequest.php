<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class DestroyRequest extends BaseRequest
{
    public function authorize()
    {
        if ($this->user()->isAdmin()) {
            return true;
        }

        if ($this->user()->isCommunityAdmin()) {
            if (Community::accessibleBy($this->user())->find($this->route('community_id'))) {
                return true;
            }
        }
        return false;
    }

    public function rules()
    {
        $rules = [];

        return $rules;
    }

    public function messages()
    {
        return [];
    }
}
