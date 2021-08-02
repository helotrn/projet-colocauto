<?php

namespace App\Http\Requests\Padlock;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        if ($user && $user->isAdmin()) {
            return true;
        }

        return false;
    }
}
