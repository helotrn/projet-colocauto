<?php

namespace Tests\Integration;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Borrower;
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
        $bill = factory(Bill::class)->create(['user_id' => $this->user->id]);
        $billItem = factory(BillItem::class)->create(['bill_id' => $bill->id]);

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
        $bill = factory(Bill::class)->create(['user_id' => $this->user->id, 'payment_method_id' => $paymentMethod->id]);
        $billItem = factory(BillItem::class)->create(['bill_id' => $bill->id]);
        $payment = factory(Payment::class)->create(['loan_id' => $loan->id, 'bill_item_id' => $billItem->id]);

        $response = $this->json('GET', "/api/v1/payments/$payment->id?loan.id=$loan->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $payment->id])
            ->assertJsonStructure(static::$getPaymentResponseStructure);
    }

    public function testCreatePaymentsWithActionsFlow() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");

        $response->assertStatus(200);

        $payment = $loan->payments->first();
        $response = $this->json('GET', "/api/v1/payments?loan.id=$loan->id");

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals($payment->id, array_get($json, 'data.0.id'));
    }

    public function testCompletePayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();

        $this->assertTrue($loan->intentions()->count() > 0);

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'completed'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }

    public function testCancelPayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();

        $this->assertTrue($loan->intentions()->count() > 0);

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/cancel");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'canceled'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }
}
