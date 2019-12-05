<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Borrower;
use App\Repositories\BorrowerRepository;

class BorrowerController extends RestController
{
    public function __construct(BorrowerRepository $repository, Borrower $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
