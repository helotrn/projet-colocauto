<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\User;
use App\Repositories\UserRepository;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function getUser(Request $request) {
        return $this->retrieve($request, $request->user()->id);
    }
}
