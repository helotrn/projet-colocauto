<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Handover;

class HandoverRepository extends RestRepository
{
    public function __construct(Handover $model) {
        $this->model = $model;
    }
}
