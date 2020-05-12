<?php

namespace Tests\Integration;

use App\Models\Takeover;
use Tests\TestCase;

class TakeoverTest extends TestCase
{
    public function testCreateTakeovers() {
        $this->markTestIncomplete();
        $data = [
            'executed_at' => $this->faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
            'status' => $this->faker->randomElement(['in_process' ,'canceled', 'completed']),
            'mileage_beginning' => $this->faker->numberBetween($min = 0, $max = 300000),
            'comments_on_vehicle' => $this->faker->sentence,
            'contested_at' => null,
            'comments_on_contestation' => null,
        ];

        $response = $this->json('POST', route('takeovers.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowTakeovers() {
        $this->markTestIncomplete();
        $post = factory(Takeover::class)->create();

        $response = $this->json('GET', route('takeovers.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateTakeovers() {
        $this->markTestIncomplete();
        $post = factory(Takeover::class)->create();
        $data = [
            'comments_on_vehicle' => $this->faker->sentence,
        ];

        $response = $this->json('PUT', route('takeovers.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTakeovers() {
        $this->markTestIncomplete();
        $post = factory(Takeover::class)->create();

        $response = $this->json('DELETE', route('takeovers.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListTakeovers() {
        $this->markTestIncomplete();
        $takeovers = factory(Takeover::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'executed_at',
                'status',
                'mileage_beginning',
                'comments_on_vehicle',
                'contested_at',
                'comments_on_contestation',
            ]);
        });

        $response = $this->json('GET', route('takeovers.index'));

        $response->assertStatus(200)
                ->assertJson($takeovers->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'executed_at',
                        'status',
                        'mileage_beginning',
                        'comments_on_vehicle',
                        'contested_at',
                        'comments_on_contestation',
                    ],
                ]);
    }
}
