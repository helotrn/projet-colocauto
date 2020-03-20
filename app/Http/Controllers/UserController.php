<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\AddBalanceRequest;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\Borrower;
use App\Models\User;
use App\Repositories\CommunityRepository;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends RestController
{
    public function __construct(
        UserRepository $repository,
        User $model,
        CommunityRepository $communityRepo
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->communityRepo = $communityRepo;
    }

    public function index(Request $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(CreateRequest $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->respondWithMessage('Not found', 404);
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id) {
        if ($id === 'me') {
            $id = $request->user()->id;
        }

        $item = $this->repo->find($request, $id);

        try {
            $item = $this->repo->find($request, $id);
            return $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }
    }

    public function destroy(DestroyRequest $request, $id) {
        try {
            return parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }
    }

    public function submit(Request $request, $id) {
        $user = $this->repo->find($request, $id);

        if (!!$user->submitted_at) {
            return $this->respondWithMessage('Already submitted.', 200);
        }

        $user->submit();

        return $this->respondWithItem($request, $user);
    }

    public function getCommunities(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);
        if ($user) {
            $request->merge(['user_id' => $userId]);
            return $this->communityRepo->get($request);
        }
    }

    public function retrieveCommunity(Request $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $this->communityRepo->find($request, $communityId);

        if ($user->id && $community->id) {
            $data = [
                'communities' => ['id' => $community->id]
            ];
            $request->merge(['user_id' => $userId]);
            $request->merge(['community_id' => $communityId]);

            return $this->repo->get($user->id, $data);
        }
        return "";
    }

    public function createCommunityUser(Request $request, $userId, $communityId) {
        $user = $this->repo->find($request, $userId);
        $community = $this->communityRepo->find($request, $communityId);

        if ($user->communities->where('id', $communityId)->isEmpty()) {
            $user->communities()->attach($community);

            return $this->respondWithItem($request, $community);
        }

        return $this->respondWithItem(
            $request,
            $user->communities->where('id', $communityId)->first()
        );
    }

    public function deleteCommunityUser(Request $request, $userId, $communityId) {
        $community = $this->communityRepo->find($request, $communityId);
        $user = $this->repo->find($request, $userId);

        if ($user->communities->where('id', $communityId)->isNotEmpty()) {
            $user->communities()->detach($community);
        }

        return $community;
    }

    public function getBalance(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);

        return $user->balance;
    }

    public function addToBalance(AddBalanceRequest $request, $userId) {
        $findRequest = $request->redirectAuth(Request::class);
        $user = $this->repo->find($findRequest, $userId);

        $transactionId = $request->get('transaction_id');
        $amount = $request->get('amount');
        $paymentMethodId = $request->get('payment_method_id');

        if ($paymentMethodId) {
            $paymentMethod = $user->paymentMethods->where('id', $paymentMethodId)->first();
        } else {
            $paymentMethod = $user->defaultPaymentMethod;
        }

        if (!$paymentMethod) {
            return $this->respondWithMessage('no_payment_method', 400);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $amountWithFeeAndTaxes = ($amount * 1.05) * 1.14975;
        $amountWithFeeAndTaxesInCents = intval($amountWithFeeAndTaxes * 100);

        if ($amountWithFeeAndTaxesInCents <= 0) {
            return $this->respondWithMessage('amount_in_cents_is_nothing', 400);
        }

        $fee = $amount * 1.05 - $amount;
        $taxes = $amount * 1.05 * 1.14975 - $amount - $fee;

        $date = date('Y-m-d');
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amountWithFeeAndTaxesInCents,
                'currency' => 'cad',
                'customer' => $user->getStripeCustomer()->id,
                'description' => "$date - Ajout au compte Locomotion: "
                    . "{$amount}$ + {$fee}$ (frais) + {$taxes}$ (taxes)",
            ]);

            $user->balance += $amount;
            $user->transaction_id = $transactionId + 1;
            $user->save();

            return response($user->balance, 200);
        } catch (\Exception $e) {
            return $this->respondWithMessage($e->getMessage(), 500);
        }
    }

    public function template(Request $request) {
        $template = [
            'item' => [
                'name' => '',
            ],
            'form' => [
                'general' => [
                    'email' => [
                        'type' => 'email',
                    ],
                    'name' => [
                        'type' => 'text',
                    ],
                    'last_name' => [
                        'type' => 'text',
                    ],
                    'description' => [
                        'type' => 'textarea',
                    ],
                    'date_of_birth' => [
                        'type' => 'date',
                    ],
                    'address' => [
                        'type' => 'text',
                    ],
                    'postal_code' => [
                        'type' => 'text',
                    ],
                    'phone' => [
                        'type' => 'text',
                    ],
                    'is_smart_phone' => [
                        'type' => 'checkbox',
                    ],
                    'other_phone' => [
                        'type' => 'text',
                    ],
                ],
                'borrower' => [
                    'noke_id' => [
                        'type' => 'text',
                    ],
                    'drivers_license_number' => [
                        'type' => 'text'
                    ],
                    'has_been_sued_last_ten_years' => [
                        'type' => 'checkbox',
                    ],
                    'insurance' => [
                        'type' => 'file',
                    ],
                    'gaa' => [
                        'type' => 'file',
                    ],
                    'saaq' => [
                        'type' => 'file',
                    ],
                ],
            ],
            'filters' => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules('template', $request->user());
        foreach ($modelRules as $field => $rules) {
            if (!isset($template['form']['borrower'][$field])) {
                continue;
            }
            $template['form']['borrower'][$field]['rules'] = $this->formatRules($rules);
        }

        return $template;
    }
}
