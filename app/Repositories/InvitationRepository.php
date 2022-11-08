<?php

namespace App\Repositories;

use App\Models\Invitation;
use Illuminate\Support\Str;

class InvitationRepository extends RestRepository
{
    public function __construct(Invitation $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    public function create($data)
    {
        $this->model->fill($data);

        // generate a random token for this new invitation
        $this->model->token = Str::random(20);

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }
}

