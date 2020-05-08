<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;

class IncidentRequest extends BaseRequest
{
    public function authorize() {
        if ($this->user()->isAdmin()) {
            return true;
        }

        return false;
    }
}
