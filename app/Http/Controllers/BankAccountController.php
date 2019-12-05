<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\BankAccount;
use App\Repositories\BankAccountRepository;

class BankAccountController extends RestController
{
    public function __construct(BankAccountRepository $repository, BankAccount $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
