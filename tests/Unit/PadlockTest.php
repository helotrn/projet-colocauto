<?php
namespace Tests\Unit;

use App\Padlock;
use Tests\TestCase;

class PadlockTest extends TestCase
{
    public function testCreatePadlocks() {
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        $this->post(route('padlocks.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdatePadlocks() {
        $post = factory(Padlock::class)->create();
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        $this->put(route('padlocks.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowPadlocks() {
        $post = factory(Padlock::class)->create();
        $this->get(route('padlocks.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeletePadlocks() {
        $post = factory(Padlock::class)->create();
        $this->delete(route('padlocks.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListPadlocks() {
        $padlocks = factory(Padlock::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'mac_address']);
        });
        $this->get(route('padlocks'))
            ->assertStatus(200)
            ->assertJson($padlocks->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'mac_address' ],
            ]);
    }
}
