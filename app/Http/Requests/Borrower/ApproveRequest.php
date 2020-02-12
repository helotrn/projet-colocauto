<?php

namespace App\Http\Requests\Borrower;

use App\Http\Requests\BaseRequest;

class ApproveRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }

    public function rules() {
        $rules = [
        ];

        return $rules;
    }

    public function messages() {
        return [
        ];
    }
}
