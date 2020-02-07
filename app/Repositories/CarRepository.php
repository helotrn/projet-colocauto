<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Car;

class CarRepository extends RestRepository
{
    public function __construct(Car $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
