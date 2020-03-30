<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CommunityController;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Community\CreateRequest;
use App\Http\Requests\Community\DestroyRequest;
use App\Http\Requests\Community\UpdateRequest;
use App\Http\Requests\Community\CommunityUserTagRequest;
use App\Models\Community;
use App\Models\User;
use App\Repositories\CommunityRepository;
use Illuminate\Validation\ValidationException;

class CommunityController extends RestController
{
    public function __construct(
        CommunityRepository $repository,
        Community $model,
        TagController $tagController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->tagController = $tagController;
    }

    public function index(Request $request) {
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
        $item = $this->repo->find($request, $id);

        try {
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

    public function indexCommunityUserTags(Request $request, $communityId, $userId) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        $request->merge([ 'community_users.id' => $user->pivot->id ]);

        return $this->tagController->index($request);
    }

    public function updateCommunityUserTags(
        CommunityUserTagRequest $request,
        $communityId,
        $userId,
        $tagId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        if (!$user) {
            return abort(404);
        }

        if ($tag = $user->pivot->tags()->find($tagId)) {
            return $this->respondWithItem($request, $tag);
        }

        $user->pivot->tags()->attach($tagId);

        return $this->respondWithItem($request, $user->pivot->tags()->find($tagId));
    }

    public function destroyCommunityUserTags(
        CommunityUserTagRequest $request,
        $communityId,
        $userId,
        $tagId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        if (!$user) {
            return abort(404);
        }

        if ($tag = $user->pivot->tags()->find($tagId)) {
            $user->pivot->tags()->detach($tagId);
            return $this->respondWithItem($request, $tag);
        }

        return abort(404);
    }


    public function template(Request $request) {
        return [
          'item' => [
            'name' => '',
            'description' => '',
            'area' => [],
            'type' => 'neighborhood',
            'pricings' => [],
          ],
          'filters' => $this->model::$filterTypes,
          'form' => [
            'name' => [
              'type' => 'text',
              'required' => true,
              'label' => 'Nom',
            ],
            'description' => [
              'type' => 'textarea',
              'required' => true,
              'label' => 'Description',
            ],
            'type' => [
              'type' => 'select',
              'label' => 'Type',
              'options' => [
                [
                  'text' => 'Privée',
                  'value' => 'private',
                ],
                [
                  'text' => 'Voisinage',
                  'value' => 'neighborhood',
                ],
                [
                  'text' => 'Quartier',
                  'value' => 'borough',
                ],
              ],
            ],
          ],
        ];
    }
}
