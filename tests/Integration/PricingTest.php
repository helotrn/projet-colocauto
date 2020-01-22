<?php

namespace Tests\Integration;

use App\Models\Pricing;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function testCreatePricings() {
        $this->markTestIncomplete();
        $data = [
            'name' => $this->faker->name,
            'object_type' => $this->faker->sentence,
            'variable' => $this->faker->randomElement(['time' ,'distance']),
            'rule' => $this->faker->sentence,
        ];

        $response = $this->json('POST', route('pricings.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowPricings() {
        $this->markTestIncomplete();
        $post = factory(Pricing::class)->create();

        $response = $this->json('GET', route('pricings.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdatePricings() {
        $this->markTestIncomplete();
        $post = factory(Pricing::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('pricings.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeletePricings() {
        $this->markTestIncomplete();
        $post = factory(Pricing::class)->create();

        $response = $this->json('DELETE', route('pricings.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListPricings() {
        $this->markTestIncomplete();
        $pricings = factory(Pricing::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'object_type',
                'variable',
                'rule',
            ]);
        });

        $response = $this->json('GET', route('pricings.index'));

        $response->assertStatus(200)
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
