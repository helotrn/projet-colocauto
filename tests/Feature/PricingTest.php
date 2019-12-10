<?php

namespace Tests\Feature;

use App\Pricing;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function testCreatePricings() {
        $data = [
            'name' => $this->faker->name,
            'object_type' => $this->faker->sentence,
            'variable' => $this->faker->randomElement(['time' ,'distance']),
            'rule' => $this->faker->sentence,
        ];
        $this->post(route('pricings.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdatePricings() {
        $post = factory(Pricing::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('pricings.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowPricings() {
        $post = factory(Pricing::class)->create();
        $this->get(route('pricings.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeletePricings() {
        $post = factory(Pricing::class)->create();
        $this->delete(route('pricings.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListPricings() {
        $pricings = factory(Pricing::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'object_type',
                'variable',
                'rule',
            ]);
        });
        $this->get(route('pricings'))
            ->assertStatus(200)
            ->assertJson($pricings->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'object_type',
                    'variable',
                    'rule',
                ],
            ]);
    }
}
