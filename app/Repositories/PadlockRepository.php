<?php

namespace App\Repositories;

use App\Models\Padlock;
use Molotov\Repositories\RestRepository;

class PadlockRepository extends RestRepository
{
    public function __construct(Padlock $model) {
        $this->model = $model;
    }
}
