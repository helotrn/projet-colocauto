<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateEmailRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();
        return $user->isAdmin() || $user->id === (int) $this->route('user_id');
    }

    public function rules() {
        $user = $this->user();

        if ($user && $user->isAdmin()) {
            return [
              'email' => [ 'required', 'email', "unique:users,email,$user->id" ],
            ];
        }

        return [
            'password' => 'required',
            'email' => [ 'required', 'email', "unique:users,email,$user->id" ],
        ];
    }
}
