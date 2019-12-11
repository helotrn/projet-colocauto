<?php
namespace Tests\Feature;

use App\Models\BillableItem;
use Tests\TestCase;

class BillableItemTest extends TestCase
{
    public function testCreateBillableItems() {
        $data = [
            'label' => $this->faker->word,
            'amount' => $this->faker->numberBetween($min = 0, $max = 300000),
            'effective_at' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];

        $response = $this->json('POST', route('billable-items.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowBillableItems() {
        $post = factory(BillableItem::class)->create();
        
        $response = $this->json('GET', route('billable-items.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateBillableItems() {
        $post = factory(BillableItem::class)->create();
        $data = [
            'label' => $this->faker->word,
        ];
        
        $response = $this->json('PUT', route('billable-items.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBillableItems() {
        $post = factory(BillableItem::class)->create();
        
        $response = $this->json('DELETE', route('billable-items.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListBillableItems() {
        $billable_items = factory(BillableItem::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'label',
                'amount',
                'effective_at',
            ]);
        });

        $response = $this->json('GET', route('billable-items.index'));

        $response->assertStatus(200)
                ->assertJson($billable_items->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'label',
                        'amount',
                        'effective_at'
                    ],
                ]);
    }
}
