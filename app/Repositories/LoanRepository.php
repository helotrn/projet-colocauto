<?php

namespace App\Repositories;

use App\Models\Loan;

class LoanRepository extends RestRepository
{
    public function __construct(Loan $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    protected function orderBy($query, $def)
    {
        // Replace '.' by '_' in column names. Eg.:
        //   borrower.user.full_name
        //   loanable.owner.user.full_name
        $def = str_replace(".", "_", $def);

        return parent::orderBy($query, $def);
    }
}
