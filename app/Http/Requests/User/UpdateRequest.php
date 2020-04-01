<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        if ($this->user()->isAdmin()) {
            return true;
        }

        return $this->user()->id === (int) $this->route('id');
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
