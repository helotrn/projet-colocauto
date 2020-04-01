<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }

    public function rules() {
        $rules = [
            'email' => 'email',
        ];

        if ($userId = $this->get('id')) {
            $rules['email'] = [
                'email',
                "unique:users,email,$userId",
            ];
        }

        return $rules;
    }
}
