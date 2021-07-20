<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Pricing\EvaluateRequest;
use App\Models\Pricing;
use App\Repositories\PricingRepository;
use Illuminate\Validation\ValidationException;

class PricingController extends RestController
{
    public function __construct(PricingRepository $repository, Pricing $model)
    {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function evaluate(EvaluateRequest $request, $id)
    {
        $query = $this->model;

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $item = $query->findOrFail($id);

        $response = $item->evaluateRule(
            $request->get("km"),
            $request->get("minutes"),
            $request->get("loanable"),
            $request->get("loan")
        );

        if (!$response) {
            return $this->respondWithMessage("Rule does not evaluate.", 400);
        }

        if (is_array($response) && count($response) !== 2) {
            return $this->respondWithMessage(
                "Rule does not evaluate properly.",
                400
            );
        }

        if (is_array($response)) {
            [$price, $insurance] = $response;
        } else {
            $price = $response;
            $insurance = 0;
        }

        return [
            "price" => $price,
            "insurance" => $insurance,
        ];
    }
}
