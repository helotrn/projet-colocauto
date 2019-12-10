<?php

namespace Tests\Feature;

use App\Models\Takeover;
use Tests\TestCase;

class TakeoverTest extends TestCase
{
    public function testCreateTakeovers() {
        $data = [
            'status' => $this->faker->randomElement(['in_process' ,'canceled', 'completed']),
            'mileage_beginning' => $this->faker->numberBetween($min = 0, $max = 300000),
            'fuel_beginning' => $this->faker->numberBetween($min = 0, $max = 100),
            'comments_on_vehicle' => $this->faker->sentence,
        ];

        $response = $this->json('POST', route('takeovers.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowTakeovers() {
        $post = factory(Takeover::class)->create();
        
        $response = $this->json('GET', route('takeovers.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateTakeovers() {
        $post = factory(Takeover::class)->create();
        $data = [
            'comments_on_vehicle' => $this->faker->sentence,
        ];
        
        $response = $this->json('PUT', route('takeovers.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTakeovers() {
        $post = factory(Takeover::class)->create();
        
        $response = $this->json('DELETE', route('takeovers.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListTakeovers() {
        $takeovers = factory(Takeover::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'status',
                'mileage_beginning',
                'fuel_beginning',
                'comments_on_vehicle',
            ]);
        });

        $response = $this->json('GET', route('takeovers.index'));

        $response->assertStatus(200)
                ->assertJson($takeovers->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'status',
                        'mileage_beginning',
                        'fuel_beginning',
                        'comments_on_vehicle',
                    ],
                ]);
    }
}
