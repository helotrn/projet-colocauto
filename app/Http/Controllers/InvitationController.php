<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Repositories\InvitationRepository;
use App\Http\Requests\Invitation\CreateRequest;
use App\Events\InvitationCreatedEvent;
use Carbon\CarbonImmutable;

use App\Http\Requests\BaseRequest as Request;

class InvitationController extends RestController
{
    public function __construct(
        InvitationRepository $repository,
        Invitation $model
    ) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(Request $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
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

    public function update(Request $request, $id)
    {
        return $this->respondWithMessage(__("validation.invitation.cannot_modify"), 403);
    }

    public function create(CreateRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new InvitationCreatedEvent($item));

        return $this->respondWithItem($request, $item, 201);
    }

    public function resend(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        if($item && $item->consumed_at !== null) {
            return $this->respondWithMessage(__("validation.invitation.cannot_resend"), 403);
        }

        try {
            $item->updated_at = CarbonImmutable::now();
            $item->save();
            event(new InvitationCreatedEvent($item));
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        if($item && $item->consumed_at !== null) {
            return $this->respondWithMessage(__("validation.invitation.cannot_deactivate"), 403);
        }

        try {
            $item->consume();
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "email" => "",
                "community" => null,
                "token" => "",
                "for_community_admin" => false,
            ],
            "form" => [
                "email" => [
                    "type" => "email",
                ],
                "for_community_admin" => [
                    "type" => "checkbox",
                ],
                "community_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "communities",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name,parent.id,parent.name",
                        ],
                    ],
                ],
                "token" => [
                    "type" => "text",
                    // readonly field, generated on the server side
                    "disabled" => true
                ],
                "consumed_at" => [
                    // readonly field, generated on the server side
                    "type" => "date",
                    "disabled" => true
                ]
            ],
            "filters" => $this->model::$filterTypes ?: new \stdClass(),
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }

}
