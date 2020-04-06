<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Molotov\Repositories\RestRepository;

class UserRepository extends RestRepository
{
    public function __construct(User $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    public function create($data) {
        $this->model->fill($data);
        if ($email = array_get($data, 'email')) {
            $this->model->email = $email;
        }
        if ($password = array_get($data, 'password')) {
            $this->model->password = $password;
        }

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }

    public function updatePassword($request, $id, $newPassword) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->password = Hash::make($newPassword);

        $this->model->save();

        return $this->model;
    }
}
