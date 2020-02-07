<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Transformers\BaseTransformer;
use App\Utils\Traits\ErrorResponseTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Validator;

class RestController extends Controller
{
    use ErrorResponseTrait;

    protected $repo;
    protected $model;

    protected function respondWithCollection(Request $request, $items, $total) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        $transformer = new $this->model::$transformer;
        $results = $items->map(function ($item) use ($transformer, $request) {
            return $transformer->transform($item, [
                'fields' => $request->getFields(),
            ]);
        });

        return new LengthAwarePaginator($results, $total, $perPage, $page);
    }

    protected function validateAndCreate(Request $request) {
        $validator = Validator::make(
            $request->all(),
            $this->model::getRules('create', $request->user()),
            $this->model::$validationMessages
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->create($request->input());
    }

    protected function respondWithItem(Request $request, $item) {
        $transformer = new $this->model::$transformer;
        $result = $transformer->transform($item, [
            'fields' => $request->getFields(),
        ]);

        return $result;
    }

    protected function validateAndUpdate(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            $this->model::getRules('update', $request->user()),
            $this->model::$validationMessages
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->update($id, $request->input());
    }

    protected function validateAndDestroy(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            $this->model::getRules('destroy', $request->user()),
            $this->model::$validationMessages
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->destroy($id);
    }

    protected function streamFile($callback, $headers) {
        $response = new StreamedResponse($callback, 200, $headers);
        $response->send();
    }
}
