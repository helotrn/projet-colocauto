<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Trailer;
use App\Repositories\TrailerRepository;

class TrailerController extends RestController
{
    public function __construct(TrailerRepository $repository, Trailer $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
