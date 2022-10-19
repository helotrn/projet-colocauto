<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\PaymentMethod\CreateRequest;
use App\Models\PaymentMethod;
use App\Repositories\PaymentMethodRepository;
use Auth;
use Stripe;

class PaymentMethodController extends RestController
{
    public function __construct(
        PaymentMethodRepository $repository,
        PaymentMethod $model
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

    public function create(CreateRequest $request)
    {
        $user = $request->user();

        if ($request->get("type") === "credit_card") {
            $customer = $user->getStripeCustomer();
            try {
                $card = Stripe::createCardBySourceId(
                    $customer->id,
                    $request->get("external_id")
                );
            } catch (ValidationException $e) {
                return $this->respondWithErrors($e->errors(), $e->getMessage());
            }

            $request->merge([
                "external_id" => $card->id,
                "country" => $card->country,
            ]);
        }

        if (!$request->get("user_id")) {
            $request->merge(["user_id" => $request->user()->id]);
        }

        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
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
        $item = $this->repo->find($request, $id);

        if ($item->type === "credit_card") {
            $user = $request->user();
            $customer = $user->getStripeCustomer();
            Stripe::deleteSource($customer->id, $item->external_id);
        }

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
                "type" => "",
            ],
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "external_id" => [
                    "type" => "text",
                ],
                "type" => [
                    "type" => "select",
                ],
                "credit_card_type" => [
                    "type" => "text",
                ],
                "four_last_digits" => [
                    "type" => "number",
                    "disabled" => true,
                ],
            ],
            "filters" => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            if (!isset($template["form"][$field])) {
                continue;
            }
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        $template["form"]["type"]["options"] = [
            [
                "text" => "Carte de crédit",
                "value" => "credit_card",
            ],
        ];
        if (Auth::user()->isAdmin()) {
            $template["form"]["type"]["options"][] = [
                "text" => "Compte bancaire",
                "value" => "bank_account",
            ];
        }

        return $template;
    }
}
