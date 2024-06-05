<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        $item = User::find($this->route("id"));
        if (
            $this->user()->isAdmin() ||
            $this->user()->isAdminOfCommunityFor($this->route("id")) ||
            ($item && $item->communities->isEmpty() && $this->user()->isCommunityAdmin())
        ) {
            return true;
        }

        return $this->user()->id === (int) $this->route("id");
    }

    public function rules()
    {
        $rules = [
            "email" => "email",
        ];

        if ($userId = $this->get("id")) {
            $rules["email"] = ["email", "unique:users,email,$userId"];
        }

        return $rules;
    }
}
