<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository extends RestRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }

    public function create($data)
    {
        $this->model->fill($data);

        if (array_key_exists("email", $data)) {
            $this->model->email = $data["email"];
        }
        if (array_key_exists("password", $data)) {
            $this->model->password = Hash::make($data["password"]);
        }
        if (array_key_exists("role", $data)) {
            $this->model->role = $data["role"];
        }

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }

    public function update($request, $id, $data)
    {
        $query = $this->model;

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->fill($data);

        if (array_key_exists("email", $data)) {
            $this->model->email = $data["email"];
        }
        if (array_key_exists("password", $data)) {
            $this->model->password = Hash::make($data["password"]);
        }
        if (array_key_exists("role", $data)) {
            $this->model->role = $data["role"];
        }

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        // Trigger borrower model 'saved' event
        if ($this->model->borrower) {
            $this->model->borrower->save();
        }

        return $this->model->find($id);
    }

    public function updatePassword($request, $id, $newPassword)
    {
        $query = $this->model;

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->password = Hash::make($newPassword);

        $this->model->save();

        return $this->model;
    }
}
