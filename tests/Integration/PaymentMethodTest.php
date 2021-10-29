<?php

namespace Tests\Integration;

use App\Models\PaymentMethod;
use Stripe;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    public static $responseStructure = [
        "name",
        "external_id",
        "type",
        "four_last_digits",
        "credit_card_type",
        "user_id",
        "is_default",
        "id",
    ];

    public function testCreatePaymentMethods()
    {
        $data = [
            "name" => $this->faker->name,
            "external_id" => $this->faker->sentence,
            "type" => "bank_account",
            "four_last_digits" => $this->faker->randomNumber(
                $nbDigits = 4,
                $strict = true
            ),
            "credit_card_type" => $this->faker->creditCardType,
            "user_id" => $this->user->id,
        ];

        $response = $this->json("POST", "/api/v1/payment_methods", $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testCreateCreditCardPaymentMethods()
    {
        $data = [
            "name" => $this->faker->name,
            "external_id" => "card_test",
            "type" => "credit_card",
            "four_last_digits" => $this->faker->randomNumber(
                $nbDigits = 4,
                $strict = true
            ),
            "credit_card_type" => $this->faker->creditCardType,
            "user_id" => $this->user->id,
        ];

        Stripe::shouldReceive("getUserCustomer")
            ->once()
            ->with($this->user)
            ->andReturn(
                (object) [
                    "id" => "cus_test",
                ]
            );

        Stripe::shouldReceive("createCardBySourceId")
            ->once()
            ->with("cus_test", "card_test")
            ->andReturn((object) ["id" => "card_test"]);

        $response = $this->json("POST", "/api/v1/payment_methods", $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowPaymentMethods()
    {
        $paymentMethod = factory(PaymentMethod::class)->create([
            "user_id" => $this->user->id,
        ]);

        $response = $this->json(
            "GET",
            "/api/v1/payment_methods/$paymentMethod->id"
        );

        $response
            ->assertStatus(200)
            ->assertJson($paymentMethod->only(static::$responseStructure));
    }

    public function testUpdatePaymentMethods()
    {
        $paymentMethod = factory(PaymentMethod::class)->create([
            "user_id" => $this->user->id,
        ]);

        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json(
            "PUT",
            "/api/v1/payment_methods/$paymentMethod->id",
            $data
        );

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeletePaymentMethods()
    {
        $paymentMethod = factory(PaymentMethod::class)->create([
            "user_id" => $this->user->id,
        ]);

        $response = $this->json(
            "DELETE",
            "/api/v1/payment_methods/$paymentMethod->id"
        );

        $response
            ->assertStatus(200)
            ->assertJson($paymentMethod->only(static::$responseStructure));
    }

    public function testDeleteCreditCardPaymentMethods()
    {
        $paymentMethod = factory(PaymentMethod::class)->create([
            "user_id" => $this->user->id,
            "external_id" => "card_test",
            "type" => "credit_card",
        ]);

        Stripe::shouldReceive("getUserCustomer")
            ->once()
            ->with($this->user)
            ->andReturn((object) ["id" => "cus_test"]);

        Stripe::shouldReceive("deleteSource")
            ->once()
            ->with("cus_test", "card_test");

        $response = $this->json(
            "DELETE",
            "/api/v1/payment_methods/$paymentMethod->id"
        );

        $response
            ->assertStatus(200)
            ->assertJson($paymentMethod->only(static::$responseStructure));
    }

    public function testListPaymentMethods()
    {
        $paymentMethods = factory(PaymentMethod::class, 2)
            ->create([
                "user_id" => $this->user->id,
            ])
            ->map(function ($paymentMethod) {
                return $paymentMethod->only(static::$responseStructure);
            });

        $response = $this->json("GET", "/api/v1/payment_methods?order=id");

        $response
            ->assertStatus(200)
            ->assertJson(["data" => $paymentMethods->toArray()])
            ->assertJsonStructure([
                "data" => [
                    "*" => static::$responseStructure,
                ],
            ]);
    }
}
