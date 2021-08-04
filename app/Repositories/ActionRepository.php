<?php

namespace App\Repositories;

use App\Models\Action;

class ActionRepository extends RestRepository
{
    public function __construct(Action $model)
    {
        $this->model = $model;
    }
}
