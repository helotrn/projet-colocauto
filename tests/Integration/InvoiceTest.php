<?php

namespace Tests\Integration;

use App\Models\Invoice;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    private static $getInvoicesResponseStructure = [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];

    private static $invoiceResponseStructure = [
        'id',
        'period',
    ];

    public function testOrderInvoicesByUserFullName() {
        $data = [
          'order' => 'user.full_name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,user.id,user.full_name',
        ];
        $response = $this->json('GET', "/api/v1/invoices/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getInvoicesResponseStructure)
            ;
    }

    public function testOrderInvoicesByCreatedAt() {
        $data = [
          'order' => 'created_at',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,user.id,user.full_name',
        ];
        $response = $this->json('GET', "/api/v1/invoices/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getInvoicesResponseStructure)
            ;
    }

    public function testOrderInvoicesByPaidAt() {
        $data = [
          'order' => 'paid_at',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,user.id,user.full_name',
        ];
        $response = $this->json('GET', "/api/v1/invoices/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getInvoicesResponseStructure)
            ;
    }

    public function testOrderInvoicesByTotal() {
        $data = [
          'order' => 'total',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,user.id,user.full_name',
        ];
        $response = $this->json('GET', "/api/v1/invoices/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getInvoicesResponseStructure)
            ;
    }

    public function testOrderInvoicesByTotalWithTaxes() {
        $data = [
          'order' => 'total_with_taxes',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,user.id,user.full_name',
        ];
        $response = $this->json('GET', "/api/v1/invoices/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getInvoicesResponseStructure)
            ;
    }

    public function testCreateInvoices() {
        $data = [
            'period' => $this->faker->word,
            'total' => 0,
            'paid_at' => null,
            'user_id' => $this->user->id,
        ];

        $response = $this->json('POST', route('invoices.create'), array_merge(
            $data,
            [
                'apply_to_balance' => false
            ]
        ));

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowInvoices() {
        $invoice = factory(Invoice::class)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('GET', route('invoices.retrieve', $invoice->id));

        $response->assertStatus(200)
            ->assertJsonStructure(self::$invoiceResponseStructure);
    }

    public function testUpdateInvoices() {
        $invoice = factory(Invoice::class)->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'period' => $this->faker->word,
        ];

        $response = $this->json('PUT', route('invoices.update', $invoice->id), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(self::$invoiceResponseStructure)
            ->assertJson($data);
    }

    public function testDeleteInvoices() {
        $invoice = factory(Invoice::class)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('DELETE', route('invoices.destroy', $invoice->id));

        $response->assertStatus(200)
            ->assertJsonStructure(self::$invoiceResponseStructure);
    }

    public function testListInvoices() {
        $invoices = factory(Invoice::class, 2)->create([
            'user_id' => $this->user->id,
        ])->map(function ($invoice) {
            return $invoice->only([
                'id',
                'period',
                'total',
            ]);
        });

        $response = $this->json('GET', route('invoices.index'));

        $response->assertStatus(200)
                ->assertJson([ 'data' => $invoices->toArray() ])
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'period',
                            'total',
                            'paid_at',
                        ],
                    ],
                ]);
    }
}
