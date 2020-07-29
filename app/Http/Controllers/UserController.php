<?php

namespace App\Http\Controllers;

use App\Events\AddedToUserBalanceEvent;
use App\Events\ClaimedUserBalanceEvent;
use App\Events\RegistrationSubmittedEvent;
use App\Exports\UsersExport;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\BorrowerStatusRequest;
use App\Http\Requests\User\AddBalanceRequest;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserTagRequest;
use App\Models\Borrower;
use App\Models\Invoice;
use App\Models\User;
use App\Repositories\CommunityRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\UserRepository;
use Cache;
use Excel;
use Mail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class UserController extends RestController
{
    protected $communityRepo;
    protected $invoiceRepo;
    protected $tagController;

    public function __construct(
        UserRepository $repository,
        User $model,
        CommunityRepository $communityRepo,
        InvoiceRepository $invoiceRepo,
        TagController $tagController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->invoiceRepo = $invoiceRepo;
        $this->communityRepo = $communityRepo;
        $this->tagController = $tagController;
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get('accept')) {
            case 'text/csv':
                $filename = $this->respondWithCsv($request, $items, $this->model);
                $base = app()->make('url')->to('/');
                return response($base . $filename, 201);
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
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

    public function updatePassword(UpdatePasswordRequest $request, $id) {
        $item = $this->repo->find($request->redirectAuth(Request::class), $id);

        if (!$request->user()->isAdmin()) {
            $currentPassword = $request->get('current');

            $loginRequest = new LoginRequest();
            $loginRequest->setMethod('POST');
            $loginRequest->request->add([
                'email' => $item->email,
                'password' => $currentPassword,
            ]);
            $response = $this->login($loginRequest);
        }

        $this->repo->updatePassword($request, $id, $request->get('new'));

        return response('', 204);
    }

    public function sendPasswordResetEmail(AdminRequest $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        $report = [];
        foreach ($items as $item) {
            $authController = app(AuthController::class);
            $passwordRequest = $request->redirectAs($item, Request::class);
            $passwordRequest->merge([
                'email' => $item->email,
            ]);
            $output = $authController->passwordRequest($passwordRequest);
            $report[] = [
                'id' => $item->id,
                'response' => $output->getData(),
            ];
        }

        return [ 'report' => $report ];
    }

    public function sendWelcomeEmail(AdminRequest $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        $report = [];
        foreach ($items as $item) {
            Mail::to($item->email, $item->name . ' ' . $item->last_name)
                ->send(new \App\Mail\RegistrationSubmitted($item));
            $report[] = [
                'id' => $item->id,
                'response' => [
                    'message' => 'sent',
                ],
            ];
        }

        return [ 'report' => $report ];
    }

    public function submit(Request $request, $id) {
        $user = $this->repo->find($request, $id);

        if (!!$user->submitted_at) {
            return $this->respondWithMessage('Already submitted.', 200);
        }

        $user->submit();

        event(new RegistrationSubmittedEvent($user));

        return $this->respondWithItem($request, $user);
    }

    public function indexCommunities(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);

        $request->merge(['user_id' => $userId]);

        return $this->communityRepo->get($request);
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

    public function indexTags(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);

        $request->merge([ 'users.id' => $userId ]);

        return $this->tagController->index($request);
    }

    public function updateTags(UserTagRequest $request, $userId, $tagId) {
        $user = $this->repo->find($request, $userId);

        if ($tag = $user->tags->find($tagId)) {
            return $this->respondWithItem($request, $tag);
        }

        $user->tags()->attach($tagId);

        return $this->respondWithItem($request, $user->tags()->find($tagId));
    }

    public function destroyTags(UserTagRequest $request, $userId, $tagId) {
        $user = $this->repo->find($request, $userId);

        if ($tag = $user->tags->find($tagId)) {
            $user->tags()->detach($tag->id);
            return $this->respondWithItem($request, $tag);
        }

        return abort(404);
    }

    public function approveBorrower(BorrowerStatusRequest $request, $id) {
        $item = $this->repo->find($request, $id);

        if (!$item->borrower->approved_at) {
            $item->borrower->approved_at = new \Carbon\Carbon;
            $item->borrower->save();
        }

        return $this->respondWithItem($request, $item->borrower);
    }

    public function retrieveBorrower(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        return $this->respondWithItem($request, $item->borrower);
    }

    public function suspendBorrower(BorrowerStatusRequest $request, $id) {
        $item = $this->repo->find($request, $id);

        if (!$item->borrower->suspended_at) {
            $item->borrower->suspended_at = new \DateTime;
            $item->borrower->save();
        }

        return $this->respondWithItem($request, $item->borrower);
    }

    public function unsuspendBorrower(BorrowerStatusRequest $request, $id) {
        $item = $this->repo->find($request, $id);

        if ($item->borrower->suspended_at) {
            $item->borrower->suspended_at = null;
            $item->borrower->save();
        }

        return $this->respondWithItem($request, $item->borrower);
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

        $amountWithFee = ($amount * 1.022 + 0.30);
        $amountWithFeeInCents = intval($amountWithFee * 100);

        if ($amountWithFeeInCents <= 0) {
            return $this->respondWithMessage('amount_in_cents_is_nothing', 400);
        }

        $fee = round($amountWithFee - $amount, 2);

        $date = date('Y-m-d');
        $charge = null;
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amountWithFeeInCents,
                'currency' => 'cad',
                'customer' => $user->getStripeCustomer()->id,
                'description' => "Ajout au compte LocoMotion: "
                    . "{$amount}$ + {$fee}$ (frais)",
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return $this->respondWithMessage("Stripe: {$message}", 500);
        }

        try {
            $user->balance += $amount;
            $user->transaction_id = $transactionId + 1;
            $user->save();

            $invoice = new Invoice;

            $invoice->period = \Carbon\Carbon::now()->locale('fr_FR')->isoFormat('LLLL');
            $invoice->user()->associate($user);
            $invoice->save();

            $invoice->billItems()->create([
                'label' => "Ajout au compte LocoMotion: "
                    . "{$amount}$ + {$fee}$ (frais)",
                'amount' => $amountWithFee,
                'item_date' => date('Y-m-d'),
                'taxes_tps' => 0,
                'taxes_tvq' => 0,
            ]);

            $invoice->payWith($paymentMethod);

            $this->triggerInvoicePaidEvent($user, $invoice);

            return response($user->balance, 200);
        } catch (\Exception $e) {
            if ($charge) {
                \Stripe\Refund::create([
                    'charge' => $charge->id,
                ]);
            }

            $user->balance -= $amount;
            $user->transaction_id = $transactionId - 1;
            $user->save();

            return $this->respondWithMessage($e->getMessage(), 500);
        }
    }

    public function claimBalance(Request $request, $userId) {
        $user = $this->repo->find($request, $userId);

        if (Cache::get("user:claim:$userId")) {
            return $this->respondWithMessage('Déjà soumis.', 429);
        }

        event(new ClaimedUserBalanceEvent($user));

        Cache::put("user:claim:$userId", 14400);

        return $this->respondWithMessage('', 201);
    }

    public function template(Request $request) {
        $template = [
            'item' => [
                'name' => '',
                'borrower' => new \stdClass,
                'accept_conditions' => false,
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
                    'avatar' => [
                        'type' => 'image',
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                    'description' => [
                        'type' => 'textarea',
                    ],
                    'date_of_birth' => [
                        'type' => 'date',
                        'initial_view' => 'year',
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
                    'opt_in_newsletter' => [
                        'type' => 'checkbox',
                    ],
                    'accept_conditions' => [
                        'type' => 'checkbox',
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
            if (!isset($template['form']['general'][$field])) {
                continue;
            }
            $template['form']['general'][$field]['rules'] = $this->formatRules($rules);
        }

        $borrowerRules = Borrower::getRules('template', $request->user());
        foreach ($modelRules as $field => $rules) {
            if (!isset($template['form']['borrower'][$field])) {
                continue;
            }
            $template['form']['borrower'][$field]['rules'] = $this->formatRules($rules);
        }

        return $template;
    }

    private function triggerInvoicePaidEvent($user, $invoice) {
        $invoiceRequest = new Request;
        $invoiceRequest->setUserResolver(function () use ($user) {
            return $user;
        });
        $invoiceRequest->merge([ 'fields' => '*,bill_items.*' ]);

        $item = $this->invoiceRepo->find($invoiceRequest, $invoice->id);
        $invoiceTransformer = new $item::$transformer;

        event(new AddedToUserBalanceEvent(
            $user,
            $invoiceTransformer->transform($item, [
                'fields' => ['*' => '*'],
            ])
        ));
    }
}
