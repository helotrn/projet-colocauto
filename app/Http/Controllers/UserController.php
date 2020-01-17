<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
