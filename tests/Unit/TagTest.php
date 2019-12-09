<?php
namespace Tests\Unit;

use App\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function testCreateTags() {
        $data = [
            'name' => $this->faker->name,
            'type' => $this->faker->randomElement(['type1']),
        ];
        $this->post(route('tags.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateTags() {
        $post = factory(Tag::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('tags.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowTags() {
        $post = factory(Tag::class)->create();
        $this->get(route('tags.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteTags() {
        $post = factory(Tag::class)->create();
        $this->delete(route('tags.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListTags() {
        $tags = factory(Tag::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'name', 'type']);
        });
        $this->get(route('tags'))
            ->assertStatus(200)
            ->assertJson($tags->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'name', 'type' ],
            ]);
    }
}
