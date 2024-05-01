<?php

namespace App\Http\Requests\ExpenseTag;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Models\User;

class Request extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin();
    }
}
