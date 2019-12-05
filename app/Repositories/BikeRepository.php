<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Bike;

class BikeRepository extends RestRepository
{
    public function __construct(Bike $model) {
        $this->model = $model;
    }
}
