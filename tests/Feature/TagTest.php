<?php

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function testCreateTags() {
        $data = [
            'name' => $this->faker->name,
            'type' => $this->faker->randomElement(['type1']),
        ];

        $response = $this->json('POST', route('tags.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowTags() {
        $post = factory(Tag::class)->create();

        $response = $this->json('GET', route('tags.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateTags() {
        $post = factory(Tag::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        
        $response = $this->json('PUT', route('tags.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTags() {
        $post = factory(Tag::class)->create();
        
        $response = $this->json('DELETE', route('tags.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListTags() {
        $tags = factory(Tag::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'name', 'type']);
        });

        $response = $this->json('GET', route('tags.index'));

        $response->assertStatus(200)
                ->assertJson($tags->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'type',
                    ],
                ]);
    }
}
