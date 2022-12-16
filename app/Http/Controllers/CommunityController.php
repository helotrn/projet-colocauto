<?php

namespace App\Http\Controllers;

use App\Events\RegistrationRejectedEvent;
use App\Exports\CommunitiesExport;
use App\Http\Controllers\CommunityController;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Community\CreateRequest;
use App\Http\Requests\Community\DestroyRequest;
use App\Http\Requests\Community\UpdateRequest;
use App\Http\Requests\Community\CommunityUserTagRequest;
use App\Models\Community;
use App\Models\User;
use App\Repositories\CommunityRepository;
use App\Repositories\UserRepository;
use Excel;
use Illuminate\Validation\ValidationException;

class CommunityController extends RestController
{
    public function __construct(
        CommunityRepository $repository,
        Community $model,
        TagController $tagController,
        UserController $userController,
        UserRepository $UserRepository
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->tagController = $tagController;
        $this->userController = $userController;
        $this->userRepo = $UserRepository;
    }

    public function index(Request $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv(
                    $request,
                    $items,
                    $this->model
                );
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function create(CreateRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(DestroyRequest $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function indexCommunityUserTags(
        Request $request,
        $communityId,
        $userId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        $request->merge(["community_users.id" => $user->pivot->id]);

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

        return $this->respondWithItem(
            $request,
            $user->pivot->tags()->find($tagId)
        );
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

    public function indexUsers(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $items = $community->users;

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv($request, $items, new User());
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function retrieveUsers(Request $request, $id, $userId)
    {
        $community = $this->repo->find($request, $id);

        $request->merge(["communities.id" => $id]);

        return $this->userController->retrieve($request, $userId);
    }

    public function createUsers(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $userId = $request->get("id");
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            $community->users()->attach($userId);

            return $this->respondWithItem($request, $user);
        }

        return $this->respondWithItem(
            $request,
            $community->users->where("id", $userId)->first()
        );
    }

    public function updateUsers(Request $request, $id, $userId)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            return $this->respondWithMessage(null, 404);
        }

        $this->userRepo->update($request, $userId, $request->json()->all());

        return $this->respondWithItem(
            $request,
            $community
                ->users()
                ->where("users.id", $userId)
                ->first()
        );
    }

    public function destroyUsers(Request $request, $communityId, $userId)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $communityId
        );
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            return $this->respondWithMessage(null, 404);
        }

        $wasApproved = !!$community->users()->find($userId)->pivot->approved_at;
        $community->users()->detach($user);

        if (!$wasApproved) {
            event(new RegistrationRejectedEvent($user, $community));
        }

        return $this->respondWithItem($request, $user);
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "chat_group_url" => "",
                "description" => "",
                "long_description" => "",
                "type" => "neighborhood",
                "pricings" => [],
            ],
            "filters" => $this->model::$filterTypes,
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "description" => [
                    "type" => "textarea",
                ],
                "long_description" => [
                    "type" => "html",
                ],
                "chat_group_url" => [
                    "type" => "text",
                ],
                "type" => [
                    "type" => "select",
                    "label" => "Type",
                    "options" => [
                        [
                            "text" => "Privée",
                            "value" => "private",
                        ],
                        [
                            "text" => "Voisinage",
                            "value" => "neighborhood",
                        ],
                        [
                            "text" => "Quartier",
                            "value" => "borough",
                        ],
                    ],
                ],
                "parent_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "communities",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                            "type" => "borough",
                        ],
                    ],
                ],
            ],
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }
}
