<?php

namespace App\Repositories;

use App\Models\Bike;
use Molotov\Repositories\RestRepository;

class BikeRepository extends RestRepository
{
    public function __construct(Bike $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
