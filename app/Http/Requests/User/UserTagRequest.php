<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UserTagRequest extends BaseRequest
{
    public function authorize() {
        if ($this->user()->isAdmin()) {
            return true;
        }

        return false;
    }
}
