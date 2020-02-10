<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\UserCommunity\AssociateRequest as AssociateCommunityRequest;
use App\Http\Requests\UserCommunity\DissociateRequest as DissociateCommunityRequest;
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
            $response = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
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

    public function associateToCommunity(AssociateCommunityRequest $request, $userId, $communityId) {
        $community = $this->communityRepo->find($request, $communityId);
        $user = $this->repo->find($request, $userId);
        $user_communities = $user->communities->pluck('id')->ToArray();

        if ($community->id) {
            try {
                $data = [
                    'communities' => [['id' => $community->id]]
                ];
                foreach ($user_communities as $user_community) {
                    array_push($data['communities'], ['id' => $user_community]);
                }

                $request->merge($data);
                $response = parent::validateAndUpdate($request, $userId);
            } catch (ValidationException $e) {
                return $this->respondWithErrors($e->errors(), $e->getMessage());
            }

            return $response;
        } else {
            return $this->respondWithErrors('Non-existent ID', 'community id does not exist');
        }
    }

    public function dissociateFromCommunity(DissociateCommunityRequest $request, $userId, $communityId) {
        $community = $this->communityRepo->find($request, $communityId);
        $user = $this->repo->find($request, $userId);
        $user_communities = $user->communities->pluck('id')->ToArray();

        if ($community->id) {
            try {
                $data = [
                    'communities' => [['id' => $community->id]]
                ];
                foreach ($user_communities as $user_community) {
                    array_push($data['communities'], ['id' => $user_community]);
                }

                $request->merge($data);
                $response = parent::validateAndUpdate($request, $userId);
            } catch (ValidationException $e) {
                return $this->respondWithErrors($e->errors(), $e->getMessage());
            }
            return $response;
        } else {
            return $this->respondWithErrors('Non-existent ID', 'community id does not exist');
        }
    }
}
