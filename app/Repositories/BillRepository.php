<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Bill;

class BillRepository extends RestRepository
{
    public function __construct(Bill $model) {
        $this->model = $model;
    }
}
