<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Takeover;

class TakeoverRepository extends RestRepository
{
    public function __construct(Takeover $model) {
        $this->model = $model;
    }
}
