<?php

namespace Tests\Feature;

use App\Incident;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    public function testCreateIncidents() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'incident_type' => $this->faker->randomElement(['accident']),
        ];
        $this->post(route('incidents.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateIncidents() {
        $post = factory(Incident::class)->create();
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];
        $this->put(route('incidents.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowIncidents() {
        $post = factory(Incident::class)->create();
        $this->get(route('incidents.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteIncidents() {
        $post = factory(Incident::class)->create();
        $this->delete(route('incidents.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListIncidents() {
        $incidents = factory(Incident::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'status', 'incident_type']);
        });
        $this->get(route('incidents'))
            ->assertStatus(200)
            ->assertJson($incidents->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'status', 'incident_type' ],
            ]);
    }
}
