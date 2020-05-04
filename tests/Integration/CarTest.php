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
        $owner = factory(Owner::class)->create([ 'user_id' => $this->user->id ]);
        $data = [
            'brand' => $this->faker->word,
            'comments' => $this->faker->paragraph,
            'engine' => $this->faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
            'has_accident_report' => false,
            'has_informed_insurer' => true,
            'instructions' => $this->faker->paragraph,
            'insurer' => $this->faker->word,
            'is_value_over_fifty_thousand' => $this->faker->boolean,
            'location_description' => $this->faker->sentence,
            'model' => $this->faker->sentence,
            'name' => $this->faker->name,
            'owner_id' => $owner->id,
            'ownership' => $this->faker->randomElement(['self', 'rental']),
            'papers_location' => $this->faker->randomElement(['in_the_car', 'to_request_with_car']),
            'plate_number' => $this->faker->shuffle('9F29J2'),
            'position' => [$this->faker->latitude, $this->faker->longitude],
            'pricing_category' => 'small',
            'transmission_mode' => $this->faker->randomElement(['automatic' ,'manual']),
            'type' => 'car',
            'year_of_circulation' => $this->faker->year($max = 'now'),
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
