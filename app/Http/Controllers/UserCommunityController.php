<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CommunityController;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\UserCommunity\CreateRequest;
use App\Http\Requests\UserCommunity\DestroyRequest;
use App\Http\Requests\UserCommunity\IndexRequest;
use App\Http\Requests\UserCommunity\RetrieveRequest;
use App\Http\Requests\UserCommunity\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;

class UserCommunityController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function create(CreateRequest $request, $id) {
        try {
            $validator = Validator::make(
                $request->all(),
                $this->model::getRules('create', $request->user()),
                $this->model::$validationMessages
            );

            if ($validator->fails()) {
                return $this->respondWithErrors($validator->errors());
            }

            $response = "TODO"; //TODO
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }
}
