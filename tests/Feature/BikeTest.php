<?php
namespace Tests\Feature;

use App\Models\Bike;
use Tests\TestCase;

class BikeTest extends TestCase
{
    public function testCreateBikes() {
        $this->markTestIncomplete();
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

    public function testShowBikes() {
        $this->markTestIncomplete();
        $post = factory(Bike::class)->create();

        $response = $this->json('GET', route('bikes.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateBikes() {
        $this->markTestIncomplete();
        $post = factory(Bike::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('bikes.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBikes() {
        $this->markTestIncomplete();
        $post = factory(Bike::class)->create();

        $response = $this->json('DELETE', route('bikes.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListBikes() {
        $this->markTestIncomplete();
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

        $response = $this->json('GET', route('bikes.index'));

        $response->assertStatus(200)
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
