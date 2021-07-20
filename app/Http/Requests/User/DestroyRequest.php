<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class DestroyRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin();
    }
}
