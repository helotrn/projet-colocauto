<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        $id = $this->route('id');
        return $this->user()->isAdmin() || ($this->user()->id == $id);
    }

    public function rules() {
        $rules = [
            'email' => 'email',
        ];

        if ($user = $this->user()) {
            $rules['email'] = "email|unique:users,email,$user->id";
        }

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
