<?php

namespace Tests\Integration;

use App\Models\Car;
use App\Models\Owner;
use Phaza\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class CarTest extends TestCase
{
    private static $getCarResponseStructure = [
        'brand',
        'comments',
        'engine',
        'has_accident_report',
        'has_informed_insurer',
        'instructions',
        'insurer',
        'is_value_over_fifty_thousand',
        'location_description',
        'model',
        'name',
        'ownership',
        'papers_location',
        'plate_number',
        'position',
        'transmission_mode',
        'year_of_circulation',
    ];

    public function testCreateCars() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $data = [
            'name' => $this->faker->name,
            'position' => [$this->faker->latitude, $this->faker->longitude],
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'brand' => $this->faker->word,
            'model' => $this->faker->sentence,
            'year_of_circulation' => $this->faker->year($max = 'now'),
            'transmission_mode' => $this->faker->randomElement(['automatic' ,'manual']),
            'engine' => $this->faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
            'plate_number' => $this->faker->shuffle('9F29J2'),
            'is_value_over_fifty_thousand' => $this->faker->boolean,
            'ownership' => $this->faker->randomElement(['self', 'rental']),
            'papers_location' => $this->faker->randomElement(['in_the_car', 'to_request_with_car']),
            'has_accident_report' => false,
            'insurer' => $this->faker->word,
            'has_informed_insurer' => true,
            'owner_id' => $owner->id,
            'type' => 'car',
        ];

        $response = $this->json('POST', '/api/v1/cars', $data);
        $response->assertStatus(201)
            ->assertJsonStructure(static::$getCarResponseStructure);
    }

    public function testShowCars() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $car = factory(Car::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('GET', "/api/v1/cars/$car->id");

        $response->assertStatus(200)
            ->assertJsonStructure(static::$getCarResponseStructure);
    }

    public function testUpdateCars() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $car = factory(Car::class)->create(['owner_id' => $owner->id]);
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/cars/$car->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteCars() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $car = factory(Car::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('DELETE', "/api/v1/cars/$car->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/cars/$car->id");
        $response->assertStatus(404);
    }

    public function testListCars() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $cars = factory(Car::class, 2)->create(['owner_id' => $owner->id])
            ->map(function ($car) {
                return $car->only(static::$getCarResponseStructure);
            });

        $response = $this->json('GET', "/api/v1/cars");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getCarResponseStructure));
    }
}
