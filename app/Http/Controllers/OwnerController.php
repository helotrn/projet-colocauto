<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Owner;
use App\Repositories\OwnerRepository;

class OwnerController extends RestController
{
    public function __construct(OwnerRepository $repository, Owner $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
