<?php

namespace App\Http\Requests;

class AdminRequest extends BaseRequest
{
    public function authorize() {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->isAdmin();
    }
}
