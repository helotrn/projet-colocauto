<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Pricing\EvaluateRequest;
use App\Models\Pricing;
use App\Repositories\PricingRepository;
use Illuminate\Validation\ValidationException;

class PricingController extends RestController
{
    public function __construct(PricingRepository $repository, Pricing $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function evaluate(EvaluateRequest $request, $id) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy($request->user());
        }

        $item = $query->findOrFail($id);

        $response = $item->evaluateRule(
            $request->get('km'),
            $request->get('minutes'),
            $request->get('loanable')
        );

        return [
            'price' => $response,
        ];
    }
}
