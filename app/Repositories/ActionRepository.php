<?php

namespace App\Repositories;

use App\Models\Action;
use Molotov\RestRepository;

class ActionRepository extends RestRepository
{
    public function __construct(Action $model) {
        $this->model = $model;
    }
}
