<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Padlock;

class PadlockRepository extends RestRepository
{
    public function __construct(Padlock $model) {
        $this->model = $model;
    }
}
