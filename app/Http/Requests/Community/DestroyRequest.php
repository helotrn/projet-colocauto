<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class DestroyRequest extends BaseRequest
{
    public function authorize()
    {
        if ($this->user()->isAdmin()) {
            return true;
        }

        if ($this->user()->isCommunityAdmin()) {
            if (Community::accessibleBy($this->user())->find($this->route('id'))) {
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
