<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\ExpenseTag;
use App\Repositories\ExpenseTagRepository;
use App\Http\Requests\ExpenseTag\Request as AdminOnlyRequest;

class ExpenseTagController extends RestController
{
    public function __construct(ExpenseTagRepository $repository, ExpenseTag $model)
    {
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

    public function create(AdminOnlyRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(AdminOnlyRequest $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function destroy(AdminOnlyRequest $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "slug" => "",
                "color" => "",
                "admin" => false,
            ],
            "form" => [
                "name" => [
                    "type" => "text",
                    "disabled" => !$request->user()->isAdmin(),
                ],
                "slug" => [
                    "type" => "text",
                    "disabled" => !$request->user()->isAdmin(),
                ],
                "color" => [
                    "type" => "select",
                    "disabled" => !$request->user()->isAdmin(),
                    "options" => [
                        [
                            "text" => "Bleu",
                            "value" => "primary",
                        ],
                        [
                            "text" => "Vert",
                            "value" => "success",
                        ],
                        [
                            "text" => "Rouge",
                            "value" => "danger",
                        ],
                        [
                            "text" => "Gris",
                            "value" => "secondary",
                        ],
                        [
                            "text" => "Noir",
                            "value" => "dark",
                        ],
                    ],
                ],
                "admin" => [
                    "type" => "checkbox",
                    "disabled" => !$request->user()->isAdmin(),
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
