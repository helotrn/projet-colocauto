<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Car;
use App\Repositories\CarRepository;

class CarController extends RestController
{
    public function __construct(CarRepository $repository, Car $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
