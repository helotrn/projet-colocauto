<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\BankAccount;

class BankAccountRepository extends RestRepository
{
    public function __construct(BankAccount $model) {
        $this->model = $model;
    }
}
