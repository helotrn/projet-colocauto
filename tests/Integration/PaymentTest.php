<?php

namespace Tests\Integration;

use App\Models\BillItem;
use App\Models\Borrower;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private static $getPaymentResponseStructure = [
        'id',
        'loan_id',
        'bill_item_id',
    ];

    public function testCreatePayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $invoice = factory(Invoice::class)->create(['user_id' => $this->user->id]);
        $billItem = factory(BillItem::class)->create(['invoice_id' => $invoice->id]);

        $data = [
            'loan_id' => $loan->id,
            'bill_item_id' => $billItem->id,
        ];

        $response = $this->json('POST', "/api/v1/payments", $data);

        $response->assertStatus(201)
            ->assertJson(['loan_id' => $loan->id])
            ->assertJson(['bill_item_id' => $billItem->id])
            ->assertJsonStructure(static::$getPaymentResponseStructure);
    }

    public function testShowPayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $paymentMethod = factory(PaymentMethod::class)->create(['user_id' => $this->user->id]);
        $invoice = factory(Invoice::class)->create(['user_id' => $this->user->id, 'payment_method_id' => $paymentMethod->id]);
        $billItem = factory(BillItem::class)->create(['invoice_id' => $invoice->id]);
        $payment = factory(Payment::class)->create(['loan_id' => $loan->id, 'bill_item_id' => $billItem->id]);

        $response = $this->json('GET', "/api/v1/payments/$payment->id?loan.id=$loan->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $payment->id])
            ->assertJsonStructure(static::$getPaymentResponseStructure);
    }
}
