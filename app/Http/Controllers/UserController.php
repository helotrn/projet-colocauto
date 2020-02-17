<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use App\Repositories\CommunityRepository;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model, CommunityRepository $communityRepo) {
        $this->repo = $repository;
        $this->model = $model;
        $this->communityRepo = $communityRepo;
    }

    public function index(Request $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(CreateRequest $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id) {
        if ($id === 'me') {
            $id = $request->user()->id;
        }

        $item = $this->repo->find($request, $id);

        try {
            $item = $this->repo->find($request, $id);
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(DestroyRequest $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }
        return $response;
    }

    public function submit(Request $request, $id) {
        $user = $this->repo->find($request, $id);

        if (!!$user->approved_at) {
            return $this->respondWithMessage('Already submitted.', 400);
        }

        $user->submit();

        return $this->respondWithItem($request, $user);
    }

    public function getCommunities(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);
        if ($user) {
            $request->merge(['user_id' => $userId]);
            return $this->communityRepo->get($request);
        }
    }

    public function retrieveCommunity(Request $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $this->communityRepo->find($request, $communityId);

        if ($user->id && $community->id) {
            $data = [
                'communities' => ['id' => $community->id]
            ];
            $request->merge(['user_id' => $userId]);
            $request->merge(['community_id' => $communityId]);

            return $this->repo->get($user->id, $data);
        }
        return "";
    }

    public function createUserCommunity(Request $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $this->communityRepo->find($request, $communityId);

        if ($user->communities->where('id', $communityId)->isEmpty()) {
            $user->communities()->attach($community);

            return $this->respondWithItem($request, $community);
        }

        return $this->respondWithItem(
            $request,
            $user->communities->where('id', $communityId)->first()
        );
    }

    public function deleteCommunity(Request $request, $userId, $communityId) {
        $community = $this->communityRepo->find($request, $communityId);
        $user = $this->repo->find($request, $userId);

        if ($user->communities->where('id', $communityId)->isNotEmpty()) {
            $user->communities()->detach($community);
        }

        return $community;
    }
}
