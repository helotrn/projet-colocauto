<?php
namespace Tests\Feature;

use App\Bike;
use Tests\TestCase;
use Phaza\LaravelPostgis\Geometries\Point;

class BikeTest extends TestCase
{
    public function testCreateBikes() {
        $data = [
            'name' => $this->faker->name,
            'position' => "{$this->faker->latitude} {$this->faker->longitude}",
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'model' => $this->faker->sentence,
            'type' => $this->faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
            'size' => $this->faker->randomElement(['big' ,'medium', 'small', 'kid']),
        ];

        $response = $this->json('POST', route('bikes.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testUpdateBikes() {
        $post = factory(Bike::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];
        $this->put(route('bikes.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowBikes() {
        $post = factory(Bike::class)->create();
        $this->get(route('bikes.retrieve', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteBikes() {
        $post = factory(Bike::class)->create();
        $this->delete(route('bikes.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListBikes() {
        $bikes = factory(Bike::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'position',
                'location_description',
                'comments',
                'instructions',
                'model',
                'type',
                'size',
            ]);
        });

        $this->get(route('bikes'))
            ->assertStatus(200)
            ->assertJson($bikes->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'position',
                    'location_description',
                    'comments',
                    'instructions',
                    'model',
                    'type',
                    'size',
                ],
            ]);
    }
}
