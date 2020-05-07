<?php

namespace App\Http\Controllers;

use App\Http\Requests\Action\PaymentRequest;
use App\Http\Requests\BaseRequest as Request;
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

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(Request $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(Request $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(Request $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function complete(PaymentRequest $request, $actionId, $loanId) {
        // Authentication
        $authRequest = $request->redirectAuth(Request::class);

        // Validation existence
        $payment = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        // Prepare variables
        $price = $loan->actual_price;
        $insurance = $loan->actual_insurance;
        $platformTip = $request->get('platform_tip');
        $expenses = 0;
        $object = $loan->loanable->name;
        $prettyDate = (new Carbon($loan->departure_at))->locale('fr_FR')->isoFormat('LLL');

        // Update loan
        $loan->final_price = $price;
        $loan->final_platform_tip = $platformTip;
        $loan->save();

        // Build line items
        $items = array_filter([
            [
                'label' => "Coût de l'emprunt de $object le $prettyDate",
                'amount' => $price,
                'item_date' => date('Y-m-d'),
                'taxes_tps' => 0,
                'taxes_tvq' => 0,
            ],
            $insurance ? [
                'label' => "Coût de l'assurance pour l'emprunt de $object le $prettyDate",
                'amount' => $insurance,
                'item_date' => date('Y-m-d'),
                'taxes_tps' => 0,
                'taxes_tvq' => 0,
            ] : null,
            $expenses ? [
                'label' => "Dépenses pour l'emprunt de $object le $prettyDate",
                'amount' => -$expenses,
                'item_date' => date('Y-m-d'),
                'taxes_tps' => 0,
                'taxes_tvq' => 0,
            ] : null,
            $platformTip ? [
                'label' => "Frais de plateforme pour l'emprunt de $object le $prettyDate",
                'amount' => round($platformTip - ($platformTip / 1.14975), 2),
                'item_date' => date('Y-m-d'),
                'taxes_tps' => round(($platformTip / 1.14975) * 0.05, 2),
                'taxes_tvq' => round(($platformTip / 1.14975) * 0.09975, 2),
            ] : null,
        ]);

        // Update invoices
        $borrower = $loan->borrower->user;
        $borrowerInvoice = $borrower->getLastInvoiceOrCreate();

        foreach ($items as $item) {
            $borrowerInvoice->billItems()->create($item);
        }

        $payment->borrower_invoice_id = $borrowerInvoice->id;

        if ($loan->loanable->owner) {
            $owner = $loan->loanable->owner->user;
            $ownerInvoice = $owner->getLastInvoiceOrCreate();

            foreach ($items as $item) {
                $item['amount'] = -$item['amount'];
                $ownerInvoice->billItems()->create($item);
            }

            $payment->owner_invoice_id = $ownerInvoice->id;
        }


        // Update balances
        $borrower->removeFromBalance($price + $insurance + $platformTip - $expenses);
        if ($loan->loanable->owner) {
            $owner->addToBalance($price + $insurance + $platformTip - $expenses);
        }

        // Save payment
        $payment->status = 'completed';
        $payment->save();

        return $payment;
    }
}
