<?php

namespace Tests\Feature;

use App\Models\Extension;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function testCreateExtensions() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'new_duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
            'comments_on_extension' => $this->faker->paragraph,
        ];

        $response = $this->json('POST', route('extensions.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowExtensions() {
        $post = factory(Extension::class)->create();
        
        $response = $this->json('GET', route('extensions.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateExtensions() {
        $post = factory(Extension::class)->create();
        $data = [
            'comments_on_extension' => $this->faker->paragraph,
        ];
        
        $response = $this->json('PUT', route('extensions.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteExtensions() {
        $post = factory(Extension::class)->create();
        
        $response = $this->json('DELETE', route('extensions.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListExtensions() {
        $extensions = factory(Extension::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'status',
                'new_duration',
                'comments_on_extension'
            ]);
        });

        $response = $this->json('GET', route('extensions.index'));

        $response->assertStatus(200)
                ->assertJson($extensions->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'status',
                        'new_duration',
                        'comments_on_extension'
                    ],
                ]);
    }
}
