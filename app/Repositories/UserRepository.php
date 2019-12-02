<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\User;

class UserRepository extends RestRepository
{
    public function __construct(User $user) {
        $this->model = $user;
    }
}
