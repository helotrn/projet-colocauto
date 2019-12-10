<?php

namespace Tests\Feature;

use App\Models\Padlock;
use Tests\TestCase;

class PadlockTest extends TestCase
{
    public function testCreatePadlocks() {
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];

        $response = $this->json('POST', route('padlocks.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowPadlocks() {
        $post = factory(Padlock::class)->create();
        
        $response = $this->json('GET', route('padlocks.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdatePadlocks() {
        $post = factory(Padlock::class)->create();
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        
        $response = $this->json('PUT', route('padlocks.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeletePadlocks() {
        $post = factory(Padlock::class)->create();
        
        $response = $this->json('DELETE', route('padlocks.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListPadlocks() {
        $padlocks = factory(Padlock::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'mac_address']);
        });

        $response = $this->json('GET', route('padlocks.index'));

        $response->assertStatus(200)
                ->assertJson($padlocks->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'mac_address',
                    ],
                ]);
    }
}
