<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Invoice\CreateRequest;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;

class InvoiceController extends RestController
{
    public function __construct(InvoiceRepository $repository, Invoice $model)
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

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv(
                    $request,
                    $items,
                    $this->model
                );
                $base = app()
                    ->make("url")
                    ->to("/");
                return response($base . $filename, 201);
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

        if ($request->get("apply_to_balance")) {
            $user = $item->user;
            $total = $item->total;

            $user->updateBalance($total);
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

    public function destroy(Request $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function template()
    {
        return [
            "item" => [
                "apply_to_balance" => true,
                "bill_items" => [],
                "period" => "",
            ],
            "rules" => [],
            "filters" => $this->model::$filterTypes,
        ];
    }
}
