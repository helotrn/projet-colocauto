<?php

namespace Tests\Integration;

use App\Models\Invoice;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    private static $invoiceResponseStructure = [
        'id',
        'period',
    ];

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
