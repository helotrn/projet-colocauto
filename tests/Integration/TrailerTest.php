<?php

namespace Tests\Integration;

use App\Models\Community;
use App\Models\Owner;
use App\Models\Trailer;
use MStaack\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class TrailerTest extends TestCase
{
    private static $getTrailerResponseStructure = [
        'id',
        'name',
        'comments',
        'instructions',
        'location_description',
        'maximum_charge',
        'position',
    ];

    public function testCreateTrailers() {
        $data = [
            'name' => $this->faker->name,
            'position' => [$this->faker->latitude, $this->faker->longitude],
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'maximum_charge' => $this->faker->numberBetween($min = 1000, $max = 9000),
            'type' => 'trailer',
        ];

        $response = $this->json('POST', '/api/v1/trailers', $data);
        $response->assertStatus(201)
            ->assertJsonStructure(static::$getTrailerResponseStructure);
    }

    public function testShowTrailers() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $trailer = factory(Trailer::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('GET', "/api/v1/trailers/$trailer->id");

        $response->assertStatus(200)
            ->assertJsonStructure(static::$getTrailerResponseStructure);
    }

    public function testUpdateTrailers() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $trailer = factory(Trailer::class)->create(['owner_id' => $owner->id]);
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/trailers/$trailer->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTrailers() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $trailer = factory(Trailer::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('DELETE', "/api/v1/trailers/$trailer->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/trailers/$trailer->id");
        $response->assertStatus(404);
    }

    public function testListTrailers() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $trailers = factory(Trailer::class, 2)->create(['owner_id' => $owner->id])
            ->map(function ($trailer) {
                return $trailer->only(static::$getTrailerResponseStructure);
            });

        $response = $this->json('GET', "/api/v1/trailers");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getTrailerResponseStructure));
    }
}
