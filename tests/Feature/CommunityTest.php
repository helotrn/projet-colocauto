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
            'area' => null,
        ];

        $response = $this->json('POST', "/api/v1/communities/", $data);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'area',
                'updated_at',
                'created_at'
            ]);
    }

    public function testCreateCommunitiesWithNullName() {
        $data = [
            'name' => null,
            'description' => $this->faker->sentence,
            'area' => null,
        ];

        $response = $this->json('POST', "/api/v1/communities/", $data);
        $response->assertStatus(422);
    }

    public function testShowCommunities() {
        $this->markTestIncomplete();
        $post = factory(Community::class)->create();

        $response = $this->json('GET', route('communities.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateCommunities() {
        $this->markTestIncomplete();
        $post = factory(Community::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('communities.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteCommunities() {
        $this->markTestIncomplete();
        $post = factory(Community::class)->create();

        $response = $this->json('DELETE', route('communities.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListCommunities() {
        $this->markTestIncomplete();
        $communities = factory(Community::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'description',
                'territory',
            ]);
        });

        $response = $this->json('GET', route('communities.index'));

        $response->assertStatus(200)
                ->assertJson($communities->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'territory',
                    ],
                ]);
    }
}
