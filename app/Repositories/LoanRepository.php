<?php

namespace App\Repositories;

use App\Models\Loan;
use Molotov\Repositories\RestRepository;

class LoanRepository extends RestRepository
{
    public function __construct(Loan $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
