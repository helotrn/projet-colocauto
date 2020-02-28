<?php

namespace App\Repositories;

use App\Models\Handover;
use Molotov\Repositories\RestRepository;

class HandoverRepository extends RestRepository
{
    public function __construct(Handover $model) {
        $this->model = $model;
    }
}
