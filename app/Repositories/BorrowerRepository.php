<?php

namespace App\Repositories;

use App\Models\Borrower;
use Molotov\RestRepository;

class BorrowerRepository extends RestRepository
{
    public function __construct(Borrower $model) {
        $this->model = $model;
    }
}
