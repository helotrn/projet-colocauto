<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Borrower;

class BorrowerRepository extends RestRepository
{
    public function __construct(Borrower $model) {
        $this->model = $model;
    }
}
