<?php
namespace Tests\Feature;

use App\Models\Bill;
use Tests\TestCase;

class BillTest extends TestCase
{
    public function testCreateBills() {
        $this->markTestIncomplete();
        $data = [
            'period' => $this->faker->word,
            'payment_method' => $this->faker->word,
            'total' => $this->faker->numberBetween($min = 0, $max = 300000),
            'paid_at' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];

        $response = $this->json('POST', route('bills.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowBills() {
        $this->markTestIncomplete();
        $post = factory(Bill::class)->create();

        $response = $this->json('GET', route('bills.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateBills() {
        $this->markTestIncomplete();
        $post = factory(Bill::class)->create();
        $data = [
            'period' => $this->faker->word,
        ];

        $response = $this->json('PUT', route('bills.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBills() {
        $this->markTestIncomplete();
        $post = factory(Bill::class)->create();

        $response = $this->json('DELETE', route('bills.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListBills() {
        $this->markTestIncomplete();
        $bills = factory(Bill::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'period',
                'payment_method',
                'total',
                'paid_at',
            ]);
        });

        $response = $this->json('GET', route('bills.index'));

        $response->assertStatus(200)
                ->assertJson($bills->toArray())
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
