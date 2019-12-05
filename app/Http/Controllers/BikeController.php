<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Bike;
use App\Repositories\BikeRepository;

class BikeController extends RestController
{
    public function __construct(BikeRepository $repository, Bike $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
