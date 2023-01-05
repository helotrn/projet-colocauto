<?php

namespace App\Repositories;

use App\Models\ExpenseTag;

class ExpenseTagRepository extends RestRepository
{
    public function __construct(ExpenseTag $model)
    {
        $this->model = $model;
    }
}
