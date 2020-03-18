<?php

namespace Tests\Integration;

use App\Models\Invoice;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    public function testCreateInvoices() {
        $this->markTestIncomplete();
        $data = [
            'period' => $this->faker->word,
            'payment_method' => $this->faker->word,
            'total' => $this->faker->numberBetween($min = 0, $max = 300000),
            'paid_at' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];

        $response = $this->json('POST', route('invoices.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowInvoices() {
        $this->markTestIncomplete();
        $post = factory(Invoice::class)->create();

        $response = $this->json('GET', route('invoices.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateInvoices() {
        $this->markTestIncomplete();
        $post = factory(Invoice::class)->create();
        $data = [
            'period' => $this->faker->word,
        ];

        $response = $this->json('PUT', route('invoices.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteInvoices() {
        $this->markTestIncomplete();
        $post = factory(Invoice::class)->create();

        $response = $this->json('DELETE', route('invoices.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListInvoices() {
        $this->markTestIncomplete();
        $invoices = factory(Invoice::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'period',
                'payment_method',
                'total',
                'paid_at',
            ]);
        });

        $response = $this->json('GET', route('invoices.index'));

        $response->assertStatus(200)
                ->assertJson($invoices->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'period',
                        'payment_method',
                        'total',
                        'paid_at',
                    ],
                ]);
    }
}
