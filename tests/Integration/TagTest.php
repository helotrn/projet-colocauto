<?php

namespace Tests\Integration;

use App\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function testCreateTags() {
        $data = [
            'name' => $this->faker->name,
            'slug' => $this->faker->name,
            'type' => $this->faker->randomElement(['tag']),
        ];

        $response = $this->json('POST', route('tags.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowTags() {
        $tag = factory(Tag::class)->create();

        $response = $this->json('GET', route('tags.retrieve', $tag->id));

        $response->assertStatus(200)->assertJson($tag->toArray());
    }

    public function testUpdateTags() {
        $tag = factory(Tag::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('tags.update', $tag->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTags() {
        $tag = factory(Tag::class)->create();

        $response = $this->json('GET', route('tags.index'));
        $response->assertStatus(200)->assertJson([ 'total' => 1 ]);

        $response = $this->json('DELETE', route('tags.destroy', $tag->id));
        $response->assertStatus(200)->assertJson($tag->toArray());

        $response = $this->json('GET', route('tags.index'));
        $response->assertStatus(200)->assertJson([ 'total' => 0 ]);
    }

    public function testListTags() {
        $tags = factory(Tag::class, 2)->create()->map(function ($tag) {
            return $tag->only(['id', 'name', 'type']);
        });

        $response = $this->json('GET', route('tags.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => $tags->toArray()
            ])->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                    ],
                ],
            ]);
    }
}
