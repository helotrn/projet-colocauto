<?php

namespace Tests\Feature;

use App\Community;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    public function testCreateCommunities() {
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
        ];
        $this->post(route('communities.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateCommunities() {
        $post = factory(Community::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('communities.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowCommunities() {
        $post = factory(Community::class)->create();
        $this->get(route('communities.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteCommunities() {
        $post = factory(Community::class)->create();
        $this->delete(route('communities.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListCommunities() {
        $communities = factory(Community::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'name', 'description']);
        });
        $this->get(route('communities'))
            ->assertStatus(200)
            ->assertJson($communities->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'name', 'description' ],
            ]);
    }
}
