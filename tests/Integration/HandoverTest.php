<?php

namespace Tests\Integration;

use App\Models\Handover;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    public function testCreateHandovers() {
        $this->markTestIncomplete();
        $data = [
            'executed_at' => $this->faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'mileage_end' => $this->faker->numberBetween($min = 0, $max = 300000),
            'fuel_end' => $this->faker->numberBetween($min = 0, $max = 100),
            'comments_by_borrower' => $this->faker->sentence,
            'comments_by_owner' => $this->faker->sentence,
            'purchases_amount' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 100000),
            'contested_at' => null,
            'comments_on_contestation' => null,
        ];

        $response = $this->json('POST', route('handovers.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowHandovers() {
        $this->markTestIncomplete();
        $post = factory(Handover::class)->create();

        $response = $this->json('GET', route('handovers.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateHandovers() {
        $this->markTestIncomplete();
        $post = factory(Handover::class)->create();
        $data = [
            'comments_by_borrower' => $this->faker->sentence,
        ];

        $response = $this->json('PUT', route('handovers.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteHandovers() {
        $this->markTestIncomplete();
        $post = factory(Handover::class)->create();

        $response = $this->json('DELETE', route('handovers.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListHandovers() {
        $this->markTestIncomplete();
        $handovers = factory(Handover::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'executed_at',
                'status',
                'mileage_end',
                'fuel_end',
                'comments_by_borrower',
                'comments_by_owner',
                'purchases_amount',
                'contested_at',
                'comments_on_contestation',
            ]);
        });

        $response = $this->json('GET', route('handovers.index'));

        $response->assertStatus(200)
                ->assertJson($handovers->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'executed_at',
                        'status',
                        'mileage_end',
                        'fuel_end',
                        'comments_by_borrower',
                        'comments_by_owner',
                        'purchases_amount',
                        'contested_at',
                        'comments_on_contestation',
                    ],
                ]);
    }
}
