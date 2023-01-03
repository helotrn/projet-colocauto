<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Repositories\ExpenseRepository;
use App\Http\Requests\Expense\CreateRequest;

use App\Http\Requests\BaseRequest as Request;

class ExpenseController extends RestController
{
    public function __construct(
        ExpenseRepository $repository,
        Expense $model
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

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "amount" => "0",
                "type" => "credit",
                "executed_at" => null
            ],
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "executed_at" => [
                    "type" => "date",
                ],
                "user_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "user",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name,last_name",
                        ],
                    ],
                ],
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "lonables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                        ],
                    ],
                ],
                "token" => [
                    "type" => "text",
                ],
            ]
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }

}
