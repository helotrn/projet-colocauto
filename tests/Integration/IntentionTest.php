<?php

namespace Tests\Integration;

use App\Models\Intention;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    public function testCreateIntentions() {
        $this->markTestIncomplete();
        $data = [
            'executed_at' => $this->faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('POST', route('intentions.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowIntentions() {
        $this->markTestIncomplete();
        $post = factory(Intention::class)->create();

        $response = $this->json('GET', route('intentions.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateIntentions() {
        $this->markTestIncomplete();
        $post = factory(Intention::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('PUT', route('intentions.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIntentions() {
        $this->markTestIncomplete();
        $post = factory(Intention::class)->create();

        $response = $this->json('DELETE', route('intentions.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListIntentions() {
        $this->markTestIncomplete();
        $intentions = factory(Intention::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'executed_at',
                'status',
            ]);
        });

        $response = $this->json('GET', route('intentions.index'));

        $response->assertStatus(200)
                ->assertJson($intentions->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'executed_at',
                        'status',
                    ],
                ]);
    }
}
