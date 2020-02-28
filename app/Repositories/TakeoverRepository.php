<?php

namespace App\Repositories;

use App\Models\Takeover;
use Molotov\Repositories\RestRepository;

class TakeoverRepository extends RestRepository
{
    public function __construct(Takeover $model) {
        $this->model = $model;
    }
}
