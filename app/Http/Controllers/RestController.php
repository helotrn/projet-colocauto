<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Http\Requests\BaseRequest as Request;
use App\Transformers\BaseTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Utils\Traits\ErrorResponseTrait;
use Validator;

class RestController extends Controller
{
    use ErrorResponseTrait;

    protected $repo;
    protected $model;

    protected function respondWithCollection(Request $request) {
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
            return $this->respondWithErrors($validator->errors());
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
            return $this->respondWithErrors($validator->errors());
        }

        return $this->repo->update($id, $request->input());
    }

    protected function streamFile($callback, $headers) {
        $response = new StreamedResponse($callback, 200, $headers);
        $response->send();
    }
}
