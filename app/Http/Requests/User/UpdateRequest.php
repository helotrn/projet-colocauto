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
