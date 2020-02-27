<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;

class DestroyRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();
        return $user->isAdmin()
            || Loanable::where('owner_id', $user->owner->id)
                ->where('id', $this->route('id'))
                ->exists();
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
}
