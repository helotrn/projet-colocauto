<?php

namespace Tests\Integration;

use App\Models\Padlock;
use Tests\TestCase;

class PadlockTest extends TestCase
{
    public function testShowPadlocks() {
        $padlock = factory(Padlock::class)->create();

        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id));

        $response->assertStatus(200)->assertJson($padlock->toArray());
    }

    public function testUpdatePadlocks() {
        $padlock = factory(Padlock::class)->create();
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id))
            ->assertJson([ 'mac_address' => $padlock->mac_address ]);

        $response = $this->json('PUT', route('padlocks.update', $padlock->id), $data);

        $response->assertStatus(200)->assertJson($data);

        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id))
            ->assertJson($data);
    }

    public function testListPadlocks() {
        $padlocks = factory(Padlock::class, 2)->create()->map(function ($padlock) {
            return $padlock->only(['id', 'mac_address']);
        });

        $response = $this->json('GET', route('padlocks.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => $padlocks->toArray()
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'mac_address',
                    ],
                ],
            ]);
    }
}
