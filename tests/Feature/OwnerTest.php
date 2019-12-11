<?php

namespace Tests\Feature;

use App\Models\Owner;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    public function testCreateOwners() {
        $data = [
            'submitted_at' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'approved_at' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];

        $response = $this->json('POST', route('owners.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowOwners() {
        $post = factory(Owner::class)->create();
        
        $response = $this->json('GET', route('owners.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateOwners() {
        $post = factory(Owner::class)->create();
        $data = [
        ];
        
        $response = $this->json('PUT', route('owners.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteOwners() {
        $post = factory(Owner::class)->create();
        
        $response = $this->json('DELETE', route('owners.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListOwners() {
        $owners = factory(Owner::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'submitted_at',
                'approved_at',
            ]);
        });

        $response = $this->json('GET', route('owners.index'));

        $response->assertStatus(200)
                ->assertJson($owners->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'submitted_at',
                        'approved_at',
                    ],
                ]);
    }
}
