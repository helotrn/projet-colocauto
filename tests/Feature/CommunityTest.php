<?php

namespace Tests\Feature;

use App\Models\Community;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    public function testCreateCommunities() {
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
        ];

        $response = $this->json('POST', route('communities.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowCommunities() {
        $post = factory(Community::class)->create();
        
        $response = $this->json('GET', route('communities.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateCommunities() {
        $post = factory(Community::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        
        $response = $this->json('PUT', route('communities.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteCommunities() {
        $post = factory(Community::class)->create();
        
        $response = $this->json('DELETE', route('communities.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListCommunities() {
        $communities = factory(Community::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'name', 'description']);
        });

        $response = $this->json('GET', route('communities.index'));

        $response->assertStatus(200)
                ->assertJson($communities->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'description',
                    ],
                ]);
    }
}
