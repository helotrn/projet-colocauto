<?php

namespace Tests\Feature;

use App\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function testCreatePayments() {
        $this->markTestIncomplete();
        $data = [
            'executed_at' => $this->faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('POST', route('payments.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowPayments() {
        $this->markTestIncomplete();
        $post = factory(Payment::class)->create();

        $response = $this->json('GET', route('payments.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdatePayments() {
        $this->markTestIncomplete();
        $post = factory(Payment::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('PUT', route('payments.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeletePayments() {
        $this->markTestIncomplete();
        $post = factory(Payment::class)->create();

        $response = $this->json('DELETE', route('payments.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListPayments() {
        $this->markTestIncomplete();
        $payments = factory(Payment::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'executed_at',
                'status',
            ]);
        });

        $response = $this->json('GET', route('payments.index'));

        $response->assertStatus(200)
                ->assertJson($payments->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'executed_at',
                        'status',
                    ],
                ]);
    }
}
