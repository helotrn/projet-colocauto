<?php

namespace App\Http\Controllers;

use App\Events\LoanPaidEvent;
use App\Http\Requests\Action\PaymentRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Invoice;
use App\Models\Loan;
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
        $loan = Loan::findOrFail($loanId);

        if (!$loan->payment || $loan->payment->id != $actionId) {
            abort(404);
        }

        if ($loan->payment->isCompleted()) {
            return $this->respondWithErrors([
                "status" => _("validation.custom.status.action_completed"),
            ]);
        }
        $this->pay($loan, false, floatval($request->get("platform_tip")));

        return $loan->payment;
    }

    /**
     * Creates the invoices and transfers the amounts from user's balance for the completion of this loan.
     *
     * @param Loan $loan
     * @param bool $isAutomated Whether this is an automated payment or triggered by user action
     * @param int|null $finalTip Tip to set in the final payment. If null, will use the loan->platform_tip value.
     * @return void
     */
    public static function pay(
        Loan $loan,
        bool $isAutomated = false,
        int $finalTip = null
    ): void {
        // Prepare variables
        $price = $loan->actual_price;
        $insurance = $loan->actual_insurance;
        $platformTip = $finalTip ?? $loan->platform_tip;
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
                "amount_type" => null,
            ],
            "insurance" => $insurance
                ? [
                    "label" => "Coût de l'assurance pour l'emprunt de $object le $prettyDate",
                    "amount" => $insurance,
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => 0,
                    "taxes_tvq" => 0,
                    "amount_type" => null,
                ]
                : null,
            "expenses" => $expenses
                ? [
                    "label" => "Dépenses pour l'emprunt de $object le $prettyDate",
                    "amount" => -$expenses,
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => 0,
                    "taxes_tvq" => 0,
                    "amount_type" => null,
                ]
                : null,
            "platform_tip" => $platformTip
                ? [
                    "label" => "Contribution volontaire pour l'emprunt de $object le $prettyDate",
                    "amount" => round($platformTip / 1.14975, 2),
                    "item_date" => date("Y-m-d"),
                    "taxes_tps" => round(($platformTip / 1.14975) * 0.05, 2),
                    "taxes_tvq" => round(($platformTip / 1.14975) * 0.09975, 2),
                    "amount_type" => null,
                ]
                : null,
        ];

        // Update invoices
        $borrowerUser = $loan->borrower->user;

        // Create an invoice as a debit, since it is a payment
        $borrowerInvoice = $borrowerUser->createInvoice("debit");
        foreach ($items as $key => $item) {
            if ($item) {
                // Determine whether the item is a debit or credit
                if ($key != "expenses") {
                    $item["amount_type"] = "debit";
                } else {
                    $item["amount_type"] = "credit";
                }

                // Create a bill item in the invoice
                $borrowerInvoice->billItems()->create($item);
            }
        }
        $borrowerInvoice->pay();

        $ownerUser = null;
        $ownerInvoice = null;
        // Create the owner invoice as soon as an owner exists.
        // This check could be removed when we can garantee that every loanable has an owner.
        if ($loan->loanable->owner) {
            $ownerUser = $loan->loanable->owner->user;

            // Create an invoice as a credit, since the owner will
            // receive this amount from the loan
            $ownerInvoice = $ownerUser->createInvoice("credit");

            if ($items["price"]) {
                // Cost of the loan will be received by the owner
                $items["price"]["amount_type"] = "credit";

                $ownerInvoice->billItems()->create($items["price"]);
            }

            if ($items["expenses"]) {
                // Expenses are deducted from the amount received by the owner
                $items["expenses"]["amount_type"] = "debit";

                $ownerInvoice->billItems()->create($items["expenses"]);
            }

            $ownerInvoice->pay();
        }

        $debitAmount = $price + $insurance + $platformTip - $expenses;
        $creditAmount = $price - $expenses;

        // Update balances
        if ($ownerUser && $borrowerUser->is($ownerUser)) {
            // If the borrower is the owner we do a single atomic addToBalance
            // or removeFromBalance instead of both calls so we can allow
            // temporarily going below a balance of zero if the final balance
            // is above zero (e.g initial balance is 0.5 => debit 1 => balance
            // is -0.5 => credit 1 ==> final balance is 0.5)
            $movement = $creditAmount - $debitAmount;

            if ($movement >= 0) {
                $ownerUser->addToBalance($movement);
            } else {
                $ownerUser->removeFromBalance($movement * -1);
            }
        } else {
            $borrowerUser->removeFromBalance($debitAmount);
            if ($ownerUser) {
                $ownerUser->refresh();
                $ownerUser->addToBalance($creditAmount);
            }
        }

        // Save payment
        // Borrower invoice is always created.
        $payment = $loan->payment;
        $payment->borrower_invoice_id = $borrowerInvoice->id;
        // If owner invoice exists, then add it to payment.
        if ($ownerInvoice) {
            $payment->owner_invoice_id = $ownerInvoice->id;
        }
        $payment->complete();
        $payment->save();

        // Send emails after an automated or manual action
        $invoiceTransformer = new Invoice::$transformer();
        if (
            !$loan->loanable->is_self_service &&
            $loan->loanable->owner &&
            $loan->total_final_cost > 0
        ) {
            if ($isAutomated) {
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
    }
}
