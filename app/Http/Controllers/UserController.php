<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Community\IndexRequest as CommunityIndexRequest;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\RetrieveRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\UserCommunity\AssociateRequest as AssociateCommunityRequest;
use App\Http\Requests\UserCommunity\DissociateRequest as DissociateCommunityRequest;
use App\Http\Requests\UserCommunity\IndexRequest as IndexCommunityRequest;
use App\Http\Requests\UserCommunity\RetrieveRequest as RetrieveCommunityRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\CommunityRepository;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(IndexRequest $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(CreateRequest $request) {
        try {
            $response = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $response = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function retrieve(RetrieveRequest $request, $id) {
        $item = $this->repo->find($request, $id);

        try {
            $item = $this->repo->find($request, $id);
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function destroy(DestroyRequest $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function getCommunities(IndexCommunityRequest $request, $userId, CommunityRepository $communityRepo) {
        $user = $this->repo->find($userId);
        if ($user) {
            $request->merge(['user_id' => $userId]);
            return $communityRepo->get($request);
        }
    }

    public function retrieveCommunity(RetrieveCommunityRequest $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $communityRepo->find($request, $communityId);

        if ($user->id && $community->id) {
            $data = [
                'communities' => ['id' => $community->id]
            ];
            $request->merge(['user_id' => $userId]);
            $request->merge(['community_id' => $communityId]);

            return $this->repo->update($user->id, $data);
        }
        return "";
    }

    public function associateToCommunity(AssociateCommunityRequest $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $communityRepo->find($request, $communityId);

        if ($user->id && $community->id) {
            $data = [
                'communities' => ['id' => $community->id]
            ];
            $request->merge(['user_id' => $userId]);
            $request->merge(['community_id' => $communityId]);

            return $this->repo->update($user->id, $data);
        }
        return "";
    }

    public function dissociateFromCommunity(DissociateCommunityRequest $request, $userId, $communityId) {
        return "";
    }
}
