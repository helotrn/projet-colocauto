<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository extends RestRepository
{
    public function __construct(Expense $model)
    {
        $this->model = $model;
    }
}

