<?php
namespace Tests\Unit;

use App\Extension;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function testCreateExtensions() {
        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
            'new_duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
            'comments_on_extension' => $this->faker->paragraph,
        ];
        $this->post(route('extensions.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateExtensions() {
        $post = factory(Extension::class)->create();
        $data = [
            'comments_on_extension' => $this->faker->paragraph,
        ];
        $this->put(route('extensions.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowExtensions() {
        $post = factory(Extension::class)->create();
        $this->get(route('extensions.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteExtensions() {
        $post = factory(Extension::class)->create();
        $this->delete(route('extensions.delete', $post->id))
            ->assertStatus(204);
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
        $this->get(route('extensions'))
            ->assertStatus(200)
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
