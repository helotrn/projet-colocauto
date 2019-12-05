<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loan;
use App\Repositories\LoanRepository;

class LoanController extends RestController
{
    public function __construct(LoanRepository $repository, Loan $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
