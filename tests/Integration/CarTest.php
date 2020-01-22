<?php

namespace Tests\Integration;

use App\Models\Car;
use Tests\TestCase;
use Phaza\LaravelPostgis\Geometries\Point;

class CarTest extends TestCase
{
    public function testCreateCars() {
        $this->markTestIncomplete();
        $data = [
            'name' => $this->faker->name,
            'position' => new Point($this->faker->latitude, $this->faker->longitude),
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'brand' => $this->faker->word,
            'model' => $this->faker->sentence,
            'year_of_circulation' => $this->faker->year($max = 'now'),
            'transmission_mode' => $this->faker->randomElement(['automatic' ,'manual']),
            'fuel' => $this->faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
            'plate_number' => $this->faker->shuffle('9F29J2'),
            'is_value_over_fifty_thousand' => $this->faker->boolean,
            'owners' => $this->faker->randomElement(['yourself', 'dealership']),
            'papers_location' => $this->faker->randomElement(['in_the_car', 'to_request_with_car']),
            'has_accident_report' => $this->faker->boolean,
            'insurer' => $this->faker->word,
            'has_informed_insurer' => $this->faker->boolean,
        ];

        $response = $this->json('POST', route('cars.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowCars() {
        $this->markTestIncomplete();
        $post = factory(Car::class)->create();

        $response = $this->json('GET', route('cars.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateCars() {
        $this->markTestIncomplete();
        $post = factory(Car::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('cars.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteCars() {
        $this->markTestIncomplete();
        $post = factory(Car::class)->create();

        $response = $this->json('DELETE', route('cars.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListCars() {
        $this->markTestIncomplete();
        $cars = factory(Car::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'position',
                'location_description',
                'comments',
                'instructions',
                'brand',
                'model',
                'year_of_circulation',
                'transmission_mode',
                'fuel',
                'plate_number',
                'is_value_over_fifty_thousand',
                'owners',
                'papers_location',
                'has_accident_report',
                'insurer',
                'has_informed_insurer',
            ]);
        });

        $response = $this->json('GET', route('cars.index'));

        $response->assertStatus(200)
                ->assertJson($cars->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'position',
                        'location_description',
                        'comments',
                        'instructions',
                        'brand',
                        'model',
                        'year_of_circulation',
                        'transmission_mode',
                        'fuel',
                        'plate_number',
                        'is_value_over_fifty_thousand',
                        'owners',
                        'papers_location',
                        'has_accident_report',
                        'insurer',
                        'has_informed_insurer',
                    ],
                ]);
    }
}
