<?php
namespace Tests\Unit;

use App\Owner;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    public function testCreateOwners() {
        $data = [
        ];
        $this->post(route('owners.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateOwners() {
        $post = factory(Owner::class)->create();
        $data = [
        ];
        $this->put(route('owners.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowOwners() {
        $post = factory(Owner::class)->create();
        $this->get(route('owners.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteOwners() {
        $post = factory(Owner::class)->create();
        $this->delete(route('owners.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListOwners() {
        $owners = factory(Owner::class, 2)->create()->map(function ($post) {
            return $post->only(['id']);
        });
        $this->get(route('owners'))
            ->assertStatus(200)
            ->assertJson($owners->toArray())
            ->assertJsonStructure([
                '*' => [ 'id' ],
            ]);
    }
}
