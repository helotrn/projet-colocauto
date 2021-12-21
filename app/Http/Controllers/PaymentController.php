<?php

namespace App\Http\Controllers;

use App\Events\LoanPaidEvent;
use App\Http\Requests\Action\PaymentRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Listeners\SendInvoiceEmail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\LoanRepository;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;

class PaymentController extends RestController
{
    public function __construct(
        PaymentRepository $repository,
        Payment $model,
        LoanRepository $loanRepository
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->loanRepo = $loanRepository;
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

    public function create(Request $request)
    {
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
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function complete(PaymentRequest $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);

        // Validation existence
        $payment = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        if ($payment->status === "completed") {
            return $this->respondWithErrors([
                "status" => _("validation.custom.status.action_completed"),
            ]);
        }

        // Prepare variables
        $price = $loan->actual_price;
        $insurance = $loan->actual_insurance;
        $platformTip = floatval($request->get("platform_tip"));
        $expenses = $loan->handover->purchases_amount;
        $object = $loan->loanable->name;
        $prettyDate = (new Carbon($loan->departure_at))
            ->locale("fr_FR")
            ->isoFormat("LLL");

        // Update loan
        $loan->final_price = $price;
        $loan->final_insurance = $insurance;
        $loan->final_platform_tip = $platformTip;
        $loan->final_purchases_amount = $expenses;
        $loan->save();

        // Build line items
        $items = [
            "price" => [
                "label" => "Coût de l'emprunt de $object le $prettyDate",
                "amount" => $price,
                "item_date" => date("Y-m-d"),
                "taxes_tps" => 0,
                "taxes_tvq" => 0,
            ],
            "insurance" => $insurance
                ? [
                    "label" => "Coût de l'assurance pour l'emprunt de $object le $prettyDate",
                    "amount" => $insurance,
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => 0,
                    "taxes_tvq" => 0,
                ]
                : null,
            "expenses" => $expenses
                ? [
                    "label" => "Dépenses pour l'emprunt de $object le $prettyDate",
                    "amount" => -$expenses,
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => 0,
                    "taxes_tvq" => 0,
                ]
                : null,
            "platform_tip" => $platformTip
                ? [
                    "label" => "Contribution volontaire pour l'emprunt de $object le $prettyDate",
                    "amount" => round($platformTip / 1.14975, 2),
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => round(($platformTip / 1.14975) * 0.05, 2),
                    "taxes_tvq" => round(($platformTip / 1.14975) * 0.09975, 2),
                ]
                : null,
        ];

        // Update invoices
        $borrowerUser = $loan->borrower->user;
        $borrowerInvoice = $borrowerUser->createInvoice();
        foreach ($items as $item) {
            if ($item) {
                $borrowerInvoice->billItems()->create($item);
            }
        }
        $borrowerInvoice->pay();

        if ($loan->loanable->owner) {
            $ownerUser = $loan->loanable->owner->user;
            $ownerInvoice = $ownerUser->createInvoice();

            if ($items["price"]) {
                $items["price"]["amount"] = -$items["price"]["amount"];
                $ownerInvoice->billItems()->create($items["price"]);
            }

            if ($items["expenses"]) {
                $items["expenses"]["amount"] = -$items["expenses"]["amount"];
                $items["expenses"]["taxes_tvq"] = -$items["expenses"][
                    "taxes_tvq"
                ];
                $items["expenses"]["taxes_tps"] = -$items["expenses"][
                    "taxes_tps"
                ];
                $ownerInvoice->billItems()->create($items["expenses"]);
            }

            $ownerInvoice->pay();
        }

        $debitAmount = $price + $insurance + $platformTip - $expenses;
        $creditAmount = $price - $expenses;

        // Update balances
        if ($borrowerUser->is($ownerUser)) {
            //if the borrower is the owner we do a single atomic addToBalance or removeFromBalance instead of both calls so we can allow temporarily going below a balance of zero if the final balance is above zero (e.g initial balance is 0.5 => debit 1 => balance is -0.5 => credit 1 ==> final balance is 0.5)
            $movement = $creditAmount - $debitAmount;

            if ($movement >= 0) {
                $ownerUser->addToBalance($movement);
            } else {
                $ownerUser->removeFromBalance($movement * -1);
            }
        } else {
            $borrowerUser->removeFromBalance($debitAmount);
            if ($loan->loanable->owner) {
                $ownerUser->refresh();
                $ownerUser->addToBalance($creditAmount);
            }
        }

        // Save payment
        $payment->borrower_invoice_id = $borrowerInvoice->id;
        if ($loan->loanable->owner) {
            $payment->owner_invoice_id = $ownerInvoice->id;
        }
        $payment->status = "completed";
        $payment->save();

        // Send emails after an automated or manual action
        $invoiceTransformer = new Invoice::$transformer();
        if ($loan->total_final_cost > 0) {
            if ($request->get("automated")) {
                event(
                    new LoanPaidEvent(
                        $borrowerUser,
                        $invoiceTransformer->transform($borrowerInvoice, [
                            "fields" => ["*" => "*"],
                        ]),
                        "Conclusion automatique de votre emprunt",
                        "<p>Votre emprunt est terminé depuis 48h. Il est désormais clôturé!</p>"
                    )
                );

                // Trigger event for owner if exists and is not also the borrower.
                if ($loan->loanable->owner && !$borrowerUser->is($ownerUser)) {
                    event(
                        new LoanPaidEvent(
                            $ownerUser,
                            $invoiceTransformer->transform($ownerInvoice, [
                                "fields" => ["*" => "*"],
                            ]),
                            null,
                            "<p>Votre emprunt est désormais clôturé!</p>"
                        )
                    );
                }
            } else {
                event(
                    new LoanPaidEvent(
                        $borrowerUser,
                        $invoiceTransformer->transform($borrowerInvoice, [
                            "fields" => ["*" => "*"],
                        ])
                    )
                );

                // Trigger event for owner if exists and is not also the borrower.
                if ($loan->loanable->owner && !$borrowerUser->is($ownerUser)) {
                    event(
                        new LoanPaidEvent(
                            $ownerUser,
                            $invoiceTransformer->transform($ownerInvoice, [
                                "fields" => ["*" => "*"],
                            ])
                        )
                    );
                }
            }
        }

        return $payment;
    }
}
