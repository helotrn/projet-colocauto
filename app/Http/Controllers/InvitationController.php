<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Repositories\InvitationRepository;
use App\Http\Requests\Invitation\CreateRequest;
use App\Http\Requests\Invitation\AcceptRequest;
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
            $user = User::where('email', $request->get("email"))->first();
            if($user) {
                $user->invitations()->save($item);
                $item->refresh();
            }
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

    public function accept(AcceptRequest $request)
    {
        $item = Invitation::where('token', $request->get("token"))->first();
        if( !$item ) {
            return $this->respondWithMessage(__("validation.invitation.invalid"), 400);
        } else if( $item->consumed_at !== null ) {
            $date = new \Carbon\Carbon($item->consumed_at);
            return $this->respondWithMessage(__("validation.invitation.consumed", [
                "email" => $item->email,
                "date" => $date->diffForHumans(),
            ]), 403);
        } else if( !$item->community ){
            return $this->respondWithMessage(__("validation.invitation.community_is_missing"), 400);
        }

        $item->consume();
        $request->user()->invitations()->save($item);
        if( !$item->community->users()->pluck('id')->contains($request->user()->id) ) {
            $item->community->users()->attach($request->user());
        }

        return $this->respondWithItem($request, $item, 200);
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
                ],
                "user_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "users",
                        "value" => "id",
                        "text" => "full_name",
                        "params" => [
                            "fields" => "id,full_name",
                        ],
                    ],
                    "disabled" => true,
                ],
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
