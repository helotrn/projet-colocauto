<?php

namespace App\Repositories;

use App\Models\Padlock;
use Molotov\RestRepository;

class PadlockRepository extends RestRepository
{
    public function __construct(Padlock $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    protected function orderBy($query, $def) {
                             // Replace '.' by '_' in column names. Eg.:
                             //   loanable.name
        $def = str_replace('.', '_', $def);

        return parent::orderBy($query, $def);
    }
}
