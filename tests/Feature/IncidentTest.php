<?php

namespace Tests\Feature;

use App\Models\Incident;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    public function testCreateIncidents() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'incident_type' => $this->faker->randomElement(['accident']),
        ];

        $response = $this->json('POST', route('incidents.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowIncidents() {
        $post = factory(Incident::class)->create();
        
        $response = $this->json('GET', route('incidents.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateIncidents() {
        $post = factory(Incident::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('PUT', route('incidents.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIncidents() {
        $post = factory(Incident::class)->create();
        
        $response = $this->json('DELETE', route('incidents.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListIncidents() {
        $incidents = factory(Incident::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'status', 'incident_type']);
        });

        $response = $this->json('GET', route('incidents.index'));

        $response->assertStatus(200)
                ->assertJson($incidents->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'status',
                        'incident_type',
                    ],
                ]);
    }
}
