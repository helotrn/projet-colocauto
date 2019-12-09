<?php
namespace Tests\Unit;

use App\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function testCreatePayments() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        $this->post(route('payments.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdatePayments() {
        $post = factory(Payment::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        $this->put(route('payments.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowPayments() {
        $post = factory(Payment::class)->create();
        $this->get(route('payments.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeletePayments() {
        $post = factory(Payment::class)->create();
        $this->delete(route('payments.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListPayments() {
        $payments = factory(Payment::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'status']);
        });
        $this->get(route('payments'))
            ->assertStatus(200)
            ->assertJson($payments->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'status' ],
            ]);
    }
}
