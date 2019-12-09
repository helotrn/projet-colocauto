<?php
namespace Tests\Unit;

use App\Takeover;
use Tests\TestCase;

class TakeoverTest extends TestCase
{
    public function testCreateTakeovers() {
        $data = [
            'status' => $this->faker->randomElement(['in_process' ,'canceled', 'completed']),
            'mileage_beginning' => $this->faker->numberBetween($min = 0, $max = 300000),
            'fuel_beginning' => $this->faker->numberBetween($min = 0, $max = 100),
            'comments_on_vehicle' => $this->faker->sentence,
        ];
        $this->post(route('takeovers.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateTakeovers() {
        $post = factory(Takeover::class)->create();
        $data = [
            'comments_on_vehicle' => $this->faker->sentence,
        ];
        $this->put(route('takeovers.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowTakeovers() {
        $post = factory(Takeover::class)->create();
        $this->get(route('takeovers.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteTakeovers() {
        $post = factory(Takeover::class)->create();
        $this->delete(route('takeovers.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListTakeovers() {
        $takeovers = factory(Takeover::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'status',
                'mileage_beginning',
                'fuel_beginning',
                'comments_on_vehicle',
            ]);
        });
        $this->get(route('takeovers'))
            ->assertStatus(200)
            ->assertJson($takeovers->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'status',
                    'mileage_beginning',
                    'fuel_beginning',
                    'comments_on_vehicle',
                ],
            ]);
    }
}
