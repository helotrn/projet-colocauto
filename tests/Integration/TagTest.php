<?php

namespace Tests\Integration;

use App\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    private static $getTagsResponseStructure = [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];

    public function testOrderTagsById() {
        $data = [
          'order' => 'id',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*',
        ];
        $response = $this->json('GET', "/api/v1/tags/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getTagsResponseStructure)
            ;
    }

    public function testOrderTagsByName() {
        $data = [
          'order' => 'name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*',
        ];
        $response = $this->json('GET', "/api/v1/tags/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getTagsResponseStructure)
            ;
    }

    public function testOrderTagsByType() {
        $data = [
          'order' => 'type',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*',
        ];
        $response = $this->json('GET', "/api/v1/tags/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getTagsResponseStructure)
            ;
    }

    public function testOrderTagsBySlug() {
        $data = [
          'order' => 'slug',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*',
        ];
        $response = $this->json('GET', "/api/v1/tags/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getTagsResponseStructure)
            ;
    }

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
