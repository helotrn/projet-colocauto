<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Community;
use App\Models\Owner;
use Phaza\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class BikeTest extends TestCase
{
    private static $getBikeResponseStructure = [
        'id',
        'name',
        'bike_type',
        'model',
        'size',
        'position',
        'location_description',
        'instructions',
        'comments',
        'availability_ics',
    ];

    public function testCreateBikes() {
        $data = [
            'name' => $this->faker->name,
            'position' => [$this->faker->latitude, $this->faker->longitude],
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'model' => $this->faker->sentence,
            'bike_type' => $this->faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
            'size' => $this->faker->randomElement(['big' ,'medium', 'small', 'kid']),
            'availability_ics' => $this->faker->sentence,
            'type' => 'bike',
        ];

        $response = $this->json('POST', '/api/v1/bikes', $data);
        $response->assertStatus(201)
            ->assertJsonStructure(static::$getBikeResponseStructure);
    }

    public function testShowBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('GET', "/api/v1/bikes/$bike->id");

        $response->assertStatus(200)
            ->assertJsonStructure(static::$getBikeResponseStructure);
    }

    public function testUpdateBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/bikes/$bike->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('DELETE', "/api/v1/bikes/$bike->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/bikes/$bike->id");
        $response->assertStatus(404);
    }

    public function testListBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bikes = factory(Bike::class, 2)->create(['owner_id' => $owner->id])
            ->map(function ($bike) {
                return $bike->only(static::$getBikeResponseStructure);
            });

        $response = $this->json('GET', "/api/v1/bikes");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getBikeResponseStructure));
    }
}
