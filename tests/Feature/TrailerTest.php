<?php

namespace Tests\Feature;

use App\Models\Trailer;
use Tests\TestCase;
use Phaza\LaravelPostgis\Geometries\Point;

class TrailerTest extends TestCase
{
    public function testCreateTrailers() {
        $this->markTestIncomplete();
        $data = [
            'name' => $this->faker->name,
            'position' => new Point($this->faker->latitude, $this->faker->longitude),
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
            'maximum_charge' => $this->faker->numberBetween($min = 1000, $max = 9000),
        ];

        $response = $this->json('POST', route('trailers.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowTrailers() {
        $this->markTestIncomplete();
        $post = factory(Trailer::class)->create();

        $response = $this->json('GET', route('trailers.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateTrailers() {
        $this->markTestIncomplete();
        $post = factory(Trailer::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('trailers.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTrailers() {
        $this->markTestIncomplete();
        $post = factory(Trailer::class)->create();

        $response = $this->json('DELETE', route('trailers.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListTrailers() {
        $this->markTestIncomplete();
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

        $response = $this->json('GET', route('trailers.index'));

        $response->assertStatus(200)
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
