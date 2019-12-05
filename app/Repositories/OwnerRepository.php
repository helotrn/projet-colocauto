<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Owner;

class OwnerRepository extends RestRepository
{
    public function __construct(Owner $model) {
        $this->model = $model;
    }
}
