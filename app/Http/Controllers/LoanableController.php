<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loanable;
use App\Repositories\LoanableRepository;

class LoanableController extends RestController
{
    public function __construct(LoanableRepository $repository, Loanable $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
