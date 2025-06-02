<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Loanable;
use App\Models\User;
use App\Repositories\ExpenseRepository;
use App\Http\Requests\Expense\CreateRequest;
use App\Http\Requests\Expense\UpdateRequest;

use App\Http\Requests\BaseRequest as Request;
use Illuminate\Validation\ValidationException;

class ExpenseController extends RestController
{
    public function __construct(
        ExpenseRepository $repository,
        Expense $model
    ) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(Request $request)
    {

        try {
            // default order is the late expense first
            if(!$request->query('order')){
                $request->merge(['order' => '-executed_at']);
            }
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

    public function create(CreateRequest $request)
    {
        try {
            $user = User::findOrFail($request->user_id);
            $loanable = Loanable::findOrFail($request->loanable_id);
            if( !$loanable->community || !$user->communities->pluck('id')->contains($loanable->community->id) ) {
                throw ValidationException::withMessages(['loanable' => trans("validation.should_belong_to_same_community.expense")]);
            }
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            if (!$request->user()->isAdmin() && !$request->user()->isCommunityAdmin()) {
                $old = $this->model->findOrFail($id);
                if( $old->locked ) {
                    return $this->respondWithErrors([[trans("validation.cannot_modify.expense")]]);
                } else {
                    // cannot modify locked param
                    $request->merge(['locked' => $old->locked]);
                }
            }

            $user = User::findOrFail($request->user_id);
            $loanable = Loanable::findOrFail($request->loanable_id);
            if( !$loanable->community || !$user->communities->pluck('id')->contains($loanable->community->id) ) {
                throw ValidationException::withMessages(['loanable' => trans("validation.should_belong_to_same_community.expense")]);
            }
            $item = parent::validateAndUpdate($request, $id);

            // update the attached loan
            if( $item->loan_id) {
                if( $item->type == 'credit' ) {
                    $item->loan->final_purchases_amount = $item->amount;
                    if($item->loan->handover) {
                        $item->loan->handover->purchases_amount = $item->amount;
                        $item->loan->handover->save();
                    }
                } else {
                    $item->loan->final_price = $item->amount;
                }
                $item->loan->save();
            }
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "amount" => "0",
                "type" => "credit",
                "executed_at" => null
            ],
            "form" => [
                "expense_tag_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "expense_tags",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name,color"
                        ]
                    ]
                ],
                "type" => [
                    "type" => "select",
                    "options" => [
                        [
                            "text" => "Débit",
                            "value" => "debit",
                        ],
                        [
                            "text" => "Crédit",
                            "value" => "credit",
                        ],
                    ]
                ],
                "name" => [
                    "type" => "text",
                ],
                "executed_at" => [
                    "type" => "date",
                ],
                "amount" => [
                    "type" => "currency",
                ],
                "user_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "users",
                        "value" => "id",
                        "text" => "full_name",
                        "params" => [
                            "fields" => "id,name,full_name",
                        ],
                    ],
                ],
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loanables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                        ],
                    ],
                ],
                "loan_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loans",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name,departure_at,reason",
                        ],
                    ],
                ],
                "locked" => [
                    "type" => "checkbox",
                ],
            ],
            "filters" => $this->model::$filterTypes ?: new \stdClass(),
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        if (!$request->user()->isAdmin() && !$request->user()->isCommunityAdmin()) {
            unset($template['form']['type']);
            unset($template['item']['type']);
            unset($template['form']['locked']);
            unset($template['item']['locked']);
        }

        return $template;
    }

}
