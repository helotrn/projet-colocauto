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

        if ($items->isEmpty()) {
            return new LengthAwarePaginator([], $total, $perPage, $page);
        }

        $transformer = new $items[0]::$transformer;
        $results = $items->map(function ($item) use ($transformer, $request) {
            return $transformer->transform($item, [
                'fields' => $request->getFields(),
            ]);
        });

        return new LengthAwarePaginator($results, $total, $perPage, $page);
    }

    protected function validateAndCreate(Request $request) {
        $input = $request->json()->all();

        $validator = Validator::make(
            $input,
            $this->model::getRules('create', $request->user()),
            $this->model::$validationMessages
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->repo->create($input);

        $fieldsRequest = new Request;
        $fieldsRequest->merge([ 'fields' => $request->get('fields') ]);

        return $this->repo->find($fieldsRequest, $item->id);
    }

    protected function respondWithItem(Request $request, $item, $status = 200) {
        $transformer = new $item::$transformer;

        $reflect = new \ReflectionClass($this->model);
        $shortName = $reflect->getShortName();
        $context = [];
        $context[$shortName] = true;

        $result = $transformer->transform($item, [
            'fields' => $request->getFields(),
            'pivot' => isset($item->pivot) ? $item->pivot->toArray() : null,
            'context' => $context,
        ]);

        return response($result, $status);
    }

    protected function validateAndUpdate(Request $request, $id) {
        $input = $request->json()->all();

        $validator = Validator::make(
            $input,
            $this->model::getRules('update', $request->user()),
            $this->model::$validationMessages
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->repo->update($id, $input);

        $fieldsRequest = new Request;
        $fieldsRequest->merge([ 'fields' => $request->get('fields') ]);

        return $this->repo->find($fieldsRequest, $item->id);
    }

    protected function validateAndDestroy(Request $request, $id) {
        $input = $request->json()->all();

        $validator = Validator::make(
            $input,
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

    protected function formatRules($rules) {
        $flipRules = array_flip($rules);

        foreach ($flipRules as $key => $rule) {
            if (strpos($key, 'in') !== false) {
                unset($flipRules[$key]);
                [$_, $values] = explode(':', $key);
                $flipRules['oneOf'] = explode(',', $values);
            } elseif (strpos($key, ':')) {
                unset($flipRules[$key]);
                [$rule, $values] = explode(':', $key);
                $flipRules[$rule] = explode(',', $values);
            } else {
                $flipRules[$key] = true;
            }
        }

        return $flipRules;
    }
}
