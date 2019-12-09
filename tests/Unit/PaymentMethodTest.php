<?php
namespace Tests\Unit;

use App\PaymentMethod;
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
        $this->post(route('payment-methods.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdatePaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('payment-methods.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowPaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        $this->get(route('payment-methods.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeletePaymentMethods() {
        $post = factory(PaymentMethod::class)->create();
        $this->delete(route('payment-methods.delete', $post->id))
            ->assertStatus(204);
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
        $this->get(route('payment-methods'))
            ->assertStatus(200)
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
