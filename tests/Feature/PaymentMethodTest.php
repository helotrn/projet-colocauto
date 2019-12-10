<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    public function testCreatePaymentMethods() {
        $data = [
            'name' => $this->faker->name,
            'external_id' => $this->faker->sentence,
            'type' => $this->faker->randomElement(['credit_card' ,'bank_account']),
            'four_last_digits' => $this->faker->randomNumber($nbDigits = 4, $strict = true),
            'credit_card_type' => $this->faker->creditCardType,
        ];

        $response = $this->json('POST', route('payment-methods.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowPaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        
        $response = $this->json('GET', route('payment-methods.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdatePaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        
        $response = $this->json('PUT', route('payment-methods.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeletePaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        
        $response = $this->json('DELETE', route('payment-methods.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListPaymentMethods() {
        $payment_methods = factory(PaymentMethod::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'external_id',
                'type',
                'four_last_digits',
                'credit_card_type',
            ]);
        });

        $response = $this->json('GET', route('payment-methods.index'));

        $response->assertStatus(200)
                ->assertJson($payment_methods->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'external_id',
                        'type',
                        'four_last_digits',
                        'credit_card_type',
                    ],
                ]);
    }
}
