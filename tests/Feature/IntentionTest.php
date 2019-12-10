<?php

namespace Tests\Feature;

use App\Models\Intention;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    public function testCreateIntentions() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('POST', route('intentions.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowIntentions() {
        $post = factory(Intention::class)->create();
        
        $response = $this->json('GET', route('intentions.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateIntentions() {
        $post = factory(Intention::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        
        $response = $this->json('PUT', route('intentions.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIntentions() {
        $post = factory(Intention::class)->create();
        
        $response = $this->json('DELETE', route('intentions.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListIntentions() {
        $intentions = factory(Intention::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'status']);
        });

        $response = $this->json('GET', route('intentions.index'));

        $response->assertStatus(200)
                ->assertJson($intentions->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'status',
                    ],
                ]);
    }
}
