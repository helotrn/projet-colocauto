<?php

namespace App\Repositories;

use App\Models\Handover;

class HandoverRepository extends RestRepository
{
    public function __construct(Handover $model)
    {
        $this->model = $model;
    }
}
