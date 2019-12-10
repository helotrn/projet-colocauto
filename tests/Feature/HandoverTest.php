<?php

namespace Tests\Feature;

use App\Handover;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    public function testCreateHandovers() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'mileage_end' => $this->faker->numberBetween($min = 0, $max = 300000),
            'fuel_end' => $this->faker->numberBetween($min = 0, $max = 100),
            'comments_by_borrower' => $this->faker->sentence,
            'comments_by_owner' => $this->faker->sentence,
            'purchases_amount' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 100000),
        ];
        $this->post(route('handovers.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateHandovers() {
        $post = factory(Handover::class)->create();
        $data = [
            'comments_by_borrower' => $this->faker->sentence,
        ];
        $this->put(route('handovers.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowHandovers() {
        $post = factory(Handover::class)->create();
        $this->get(route('handovers.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteHandovers() {
        $post = factory(Handover::class)->create();
        $this->delete(route('handovers.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListHandovers() {
        $handovers = factory(Handover::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'status',
                'mileage_end',
                'fuel_end',
                'comments_by_borrower',
                'comments_by_owner',
                'purchases_amount',
            ]);
        });
        $this->get(route('handovers'))
            ->assertStatus(200)
            ->assertJson($handovers->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'status',
                    'mileage_end',
                    'fuel_end',
                    'comments_by_borrower',
                    'comments_by_owner',
                    'purchases_amount',
                ],
            ]);
    }
}
