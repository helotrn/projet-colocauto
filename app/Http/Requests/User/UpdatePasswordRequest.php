<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdatePasswordRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        return $user->isAdmin() || $user->id === (int) $this->route("user_id");
    }

    public function rules()
    {
        $user = $this->user();

        if ($user && $user->isAdmin()) {
            return [
                "new" => "required",
            ];
        }

        return [
            "current" => "required",
            "new" => ["required"],
        ];
    }
}
