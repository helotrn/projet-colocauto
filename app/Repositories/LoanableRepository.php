<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loanable;

class LoanableRepository extends RestRepository
{
    public function __construct(Loanable $model) {
        $this->model = $model;
    }
}
