<?php

namespace App\Repositories;

use App\Models\Car;
use Molotov\RestRepository;

class CarRepository extends RestRepository
{
    public function __construct(Car $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
