<?php

namespace App\Repositories;

use App\Models\Community;

class CommunityRepository extends RestRepository
{
    public function __construct(Community $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    protected function orderBy($query, $def)
    {
        // Replace '.' by '_' in column names. Eg.:
        //   parent.name
        $def = str_replace(".", "_", $def);

        return parent::orderBy($query, $def);
    }
}
