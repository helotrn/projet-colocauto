<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loan;

class LoanRepository extends RestRepository
{
    public function __construct(Loan $model) {
        $this->model = $model;
    }
}
