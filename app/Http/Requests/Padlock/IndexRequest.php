<?php

namespace App\Http\Requests\Padlock;

use App\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
