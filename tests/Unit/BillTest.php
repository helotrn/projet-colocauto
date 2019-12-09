<?php
namespace Tests\Unit;

use App\Bill;
use Tests\TestCase;

class BillTest extends TestCase
{
    public function testCreateBills() {
        $data = [
            'period' => $this->faker->word,
            'payment_method' => $this->faker->word,
            'total' => $this->faker->numberBetween($min = 0, $max = 300000),
        ];
        $this->post(route('bills.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateBills() {
        $post = factory(Bill::class)->create();
        $data = [
            'period' => $this->faker->word,
        ];
        $this->put(route('bills.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowBills() {
        $post = factory(Bill::class)->create();
        $this->get(route('bills.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteBills() {
        $post = factory(Bill::class)->create();
        $this->delete(route('bills.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListBills() {
        $bills = factory(Bill::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'period',
                'payment_method',
                'total',
            ]);
        });
        $this->get(route('bills'))
            ->assertStatus(200)
            ->assertJson($bills->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'period',
                    'payment_method',
                    'total',
                ],
            ]);
    }
}
