<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testCreateUsers() {
        $data = [
            'name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'google_id' => null,
            'description' => null,
            'date_of_birth' => null,
            'address' => '',
            'postal_code' => '',
            'phone' => '',
            'is_smart_phone' => false,
            'other_phone' => '',
            'approved_at' => null,
            'remember_token' => Str::random(10),
        ];

        $response = $this->json('POST', route('users.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowUsers() {
        $post = factory(User::class)->create();
        
        $response = $this->json('GET', route('users.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateUsers() {
        $post = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('users.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteUsers() {
        $post = factory(User::class)->create();
        
        $response = $this->json('DELETE', route('users.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListUsers() {
        $users = factory(User::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'email',
                'email_verified_at',
                'google_id',
                'description',
                'date_of_birth',
                'address',
                'postal_code',
                'phone',
                'is_smart_phone',
                'other_phone',
                'approved_at',
            ]);
        });

        $response = $this->json('GET', route('users.index'));

        $response->assertStatus(200)
                ->assertJson($users->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'google_id',
                        'description',
                        'date_of_birth',
                        'address',
                        'postal_code',
                        'phone',
                        'is_smart_phone',
                        'other_phone',
                        'approved_at',
                    ],
                ]);
    }
}
