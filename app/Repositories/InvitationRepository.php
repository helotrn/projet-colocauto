<?php

namespace App\Repositories;

use App\Models\Invitation;

class InvitationRepository extends RestRepository
{
    public function __construct(Invitation $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}

