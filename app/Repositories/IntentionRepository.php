<?php

namespace App\Repositories;

use App\Models\Intention;

class IntentionRepository extends RestRepository
{
    public function __construct(Intention $model)
    {
        $this->model = $model;
    }
}
