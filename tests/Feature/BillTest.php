<?php
namespace Tests\Feature;

use App\Models\Bill;
use Tests\TestCase;

class BillTest extends TestCase
{
    public function testCreateBills() {
        $data = [
            'period' => $this->faker->word,
            'payment_method' => $this->faker->word,
            'total' => $this->faker->numberBetween($min = 0, $max = 300000),
        ];

        $response = $this->json('POST', route('bills.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowBills() {
        $post = factory(Bill::class)->create();
        
        $response = $this->json('GET', route('bills.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateBills() {
        $post = factory(Bill::class)->create();
        $data = [
            'period' => $this->faker->word,
        ];
        
        $response = $this->json('PUT', route('bills.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBills() {
        $post = factory(Bill::class)->create();
        
        $response = $this->json('DELETE', route('bills.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
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

        $response = $this->json('GET', route('bills.index'));

        $response->assertStatus(200)
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
