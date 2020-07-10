<?php

namespace App\Http\Requests\Padlock;

use App\Http\Requests\BaseRequest;

class RestoreRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }
}
