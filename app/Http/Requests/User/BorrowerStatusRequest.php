<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class BorrowerStatusRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin();
    }
}
