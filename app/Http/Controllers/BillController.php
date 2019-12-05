<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Bill;
use App\Repositories\BillRepository;

class BillController extends RestController
{
    public function __construct(BillRepository $repository, Bill $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
