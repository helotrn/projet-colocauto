<?php
namespace Tests\Unit;

use App\Intention;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    public function testCreateIntentions() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        $this->post(route('intentions.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateIntentions() {
        $post = factory(Intention::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        $this->put(route('intentions.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowIntentions() {
        $post = factory(Intention::class)->create();
        $this->get(route('intentions.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteIntentions() {
        $post = factory(Intention::class)->create();
        $this->delete(route('intentions.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListIntentions() {
        $intentions = factory(Intention::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'status']);
        });
        $this->get(route('intentions'))
            ->assertStatus(200)
            ->assertJson($intentions->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'status' ],
            ]);
    }
}
