<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\User;

class UserRepository extends RestRepository
{
    public function __construct(User $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    public function create($data) {
        $this->model->fill($data);
        if ($email = dig($data, 'email')) {
            $this->model->email = $email;
        }
        if ($password = dig($data, 'password')) {
            $this->model->password = $password;
        }

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }
}
