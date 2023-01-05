<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Repositories\RefundRepository;
use App\Http\Requests\Refund\CreateRequest;

use App\Http\Requests\BaseRequest as Request;

class RefundController extends RestController
{
    public function __construct(
        RefundRepository $repository,
        Refund $model
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

    public function create(CreateRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "amount" => "0",
            ],
            "form" => [
                "executed_at" => [
                    "type" => "date",
                ],
                "amount" => [
                    "type" => "currency",
                ],
                "user_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "users",
                        "value" => "id",
                        "text" => "full_name",
                        "params" => [
                            "fields" => "id,name,full_name",
                        ],
                    ],
                ],
                "credited_user_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "users",
                        "value" => "id",
                        "text" => "full_name",
                        "params" => [
                            "fields" => "id,name,full_name",
                        ],
                    ],
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
