<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Repositories\ExpenseRepository;
use App\Http\Requests\Expense\CreateRequest;
use App\Http\Requests\Expense\UpdateRequest;

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

    public function update(UpdateRequest $request, $id)
    {
        try {
            if (!$request->user()->isAdmin()) {
                $old = $this->model->findOrFail($id);
                if( $old->locked ) {
                    return $this->respondWithErrors([[trans("validation.cannot_modify.expense")]]);
                }
            }
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
                "name" => "",
                "amount" => "0",
                "type" => "credit",
                "executed_at" => null
            ],
            "form" => [
                "expense_tag_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "expense_tags",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name,color"
                        ]
                    ]
                ],
                "type" => [
                    "type" => "select",
                    "options" => [
                        [
                            "text" => "Débit",
                            "value" => "debit",
                        ],
                        [
                            "text" => "Crédit",
                            "value" => "credit",
                        ],
                    ]
                ],
                "name" => [
                    "type" => "text",
                ],
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
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loanables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                        ],
                    ],
                ],
                "locked" => [
                    "type" => "checkbox",
                ],
            ],
            "filters" => $this->model::$filterTypes ?: new \stdClass(),
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        if (!$request->user()->isAdmin()) {
            unset($template['form']['type']);
            unset($template['item']['type']);
            unset($template['form']['locked']);
            unset($template['item']['locked']);
        }

        return $template;
    }

}
