<?php

namespace App\Repositories;

use App\Models\Bike;

class BikeRepository extends RestRepository
{
    public function __construct(Bike $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
