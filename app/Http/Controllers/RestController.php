<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Http\Requests\BaseRequest as Request;
use App\Transformers\BaseTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Validator;

class RestController extends Controller
{
    protected $repo;
    protected $model;

    public function index(Request $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }

        $transformer = new $this->model->getTransformer();
        $results = $items->map(function ($item) use ($transformer, $request) {
            return $transformer->transform($item, [
                'fields' => $request->getFields(),
            ]);
        });

        return new LengthAwarePaginator($results, $total, $perPage, $page);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), $this->model::getRules(), $this->model::$validationMessages);

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        return $this->repo->create($request->input());
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        $transformer = new $this->model::$transformer();
        $result = $transformer->transform($item, [
            'fields' => $request->getFields(),
        ]);

        return $result;
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), $this->model::getRules(), $this->model::$validationMessages);

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        return $this->repo->update($id, $request->input());
    }

    public function delete(Request $request, $id) {
        return $this->repo->delete($id);
    }

    protected function streamFile($callback, $headers) {
        $response = new StreamedResponse($callback, 200, $headers);
        $response->send();
    }

    protected function respondWithErrors($errors, $message = null) {
        return response()->json([
            'message' => $message ?: 'Validation error',
            'errors' => $errors,
        ], 422);
    }
}
