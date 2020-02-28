<?php

namespace App\Repositories;

use App\Models\Loanable;
use Molotov\Repositories\RestRepository;

class LoanableRepository extends RestRepository
{
    public function __construct(Loanable $model) {
        $this->model = $model;
    }
}
