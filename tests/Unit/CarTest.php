<?php
namespace Tests\Unit;

use App\Car;
use Tests\TestCase;
use Phaza\LaravelPostgis\Geometries\Point;

class CarTest extends TestCase
{
    public function testCreateCars() {
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
        $this->post(route('cars.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateCars() {
        $post = factory(Car::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('cars.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowCars() {
        $post = factory(Car::class)->create();
        $this->get(route('cars.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteCars() {
        $post = factory(Car::class)->create();
        $this->delete(route('cars.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListCars() {
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
        $this->get(route('cars'))
            ->assertStatus(200)
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
