<?php
namespace Tests\Unit;

use App\BillableItem;
use Tests\TestCase;

class BillableItemTest extends TestCase
{
    public function testCreateBillableItems() {
        $data = [
            'label' => $this->faker->word,
            'amount' => $this->faker->numberBetween($min = 0, $max = 300000),
        ];
        $this->post(route('billable-items.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateBillableItems() {
        $post = factory(BillableItem::class)->create();
        $data = [
            'label' => $this->faker->word,
        ];
        $this->put(route('billable-items.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowBillableItems() {
        $post = factory(BillableItem::class)->create();
        $this->get(route('billable-items.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteBillableItems() {
        $post = factory(BillableItem::class)->create();
        $this->delete(route('billable-items.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListBillableItems() {
        $billable_items = factory(BillableItem::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'label',
                'amount'
            ]);
        });
        $this->get(route('billable-items'))
            ->assertStatus(200)
            ->assertJson($billable_items->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'label',
                    'amount',
                ],
            ]);
    }
}
