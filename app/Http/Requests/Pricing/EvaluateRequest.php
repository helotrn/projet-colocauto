<?php

namespace App\Http\Requests\Pricing;

use App\Http\Requests\BaseRequest;

class EvaluateRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function rules() {
        $rules = [
            'km' => ['integer', 'required'],
            'minutes' => ['integer', 'required'],
            'object' => ['nullable'],
        ];

        return $rules;
    }

    public function messages() {
        return [];
    }
}
