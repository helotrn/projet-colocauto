<?php

namespace Tests\Feature;

use App\Trailer;
use Tests\TestCase;
use Phaza\LaravelPostgis\Geometries\Point;

class TrailerTest extends TestCase
{
    public function testCreateTrailers() {
        $data = [
            'name' => $this->faker->name,
            'position' => new Point($this->faker->latitude, $this->faker->longitude),
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
            'maximum_charge' => $this->faker->numberBetween($min = 1000, $max = 9000),
        ];
        $this->post(route('trailers.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateTrailers() {
        $post = factory(Trailer::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('trailers.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowTrailers() {
        $post = factory(Trailer::class)->create();
        $this->get(route('trailers.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteTrailers() {
        $post = factory(Trailer::class)->create();
        $this->delete(route('trailers.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListTrailers() {
        $trailers = factory(Trailer::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'position',
                'location_description',
                'comments',
                'instructions',
                'type',
                'maximum_charge',
            ]);
        });
        $this->get(route('trailers'))
            ->assertStatus(200)
            ->assertJson($trailers->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'position',
                    'location_description',
                    'comments',
                    'instructions',
                    'type',
                    'maximum_charge',
                ],
            ]);
    }
}
