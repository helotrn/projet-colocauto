<?php

namespace App\Repositories;

use App\Models\Loanable;

class LoanableRepository extends RestRepository
{
    public function __construct(Loanable $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    protected function orderBy($query, $def)
    {
        // Replace '.' by '_' in column names. Eg.:
        //   owner.user.full_name
        $def = str_replace(".", "_", $def);

        return parent::orderBy($query, $def);
    }
}
