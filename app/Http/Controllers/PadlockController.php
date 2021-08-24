<?php

namespace App\Http\Controllers;

use App\Http\Requests\Padlock\CreateRequest;
use App\Http\Requests\Padlock\IndexRequest;
use App\Http\Requests\Padlock\RestoreRequest;
use App\Http\Requests\Padlock\RetrieveRequest;
use App\Http\Requests\Padlock\UpdateRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Padlock;
use App\Repositories\PadlockRepository;

class PadlockController extends RestController
{
    public function __construct(PadlockRepository $repository, Padlock $model)
    {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(IndexRequest $request)
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

    public function retrieve(RetrieveRequest $request, $id)
    {
        $item = $this->repo->find($request, $id);

        try {
            $item = $this->repo->find($request, $id);
            return $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }
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

    public function restore(RestoreRequest $request, $id)
    {
        try {
            $response = parent::validateAndRestore($request, $id);
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
                "mac_address" => "",
                "external_id" => "",
                "loanable_id" => null,
            ],
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "mac_address" => [
                    "type" => "text",
                ],
                "external_id" => [
                    "type" => "text",
                ],
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loanables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                            "!type" => "car",
                        ],
                    ],
                ],
            ],
            "filters" => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }
}
