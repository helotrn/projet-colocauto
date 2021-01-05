<?php

namespace App\Repositories;

use App\Models\Intention;
use Molotov\RestRepository;

class IntentionRepository extends RestRepository
{
    public function __construct(Intention $model) {
        $this->model = $model;
    }
}
