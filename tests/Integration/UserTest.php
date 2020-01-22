<?php

namespace Tests\Integration;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    public function testCreateUsers() {
        $data = [
            'name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'description' => null,
            'date_of_birth' => null,
            'address' => '',
            'postal_code' => '',
            'phone' => '',
            'is_smart_phone' => false,
            'other_phone' => '',
            'remember_token' => Str::random(10),
        ];

        $response = $this->json('POST', "/api/v1/users", $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'last_name',
                'email',
                'description',
                'date_of_birth',
                'address',
                'postal_code',
                'phone',
                'is_smart_phone',
                'other_phone',
                'created_at',
                'updated_at'
            ]);
    }

    public function testShowUsers() {
        $post = factory(User::class)->create();
        $data = [];

        $response = $this->json('GET', "/api/v1/users/$post->id", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'last_name',
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
                'remember_token',
                'created_at',
                'updated_at',
                'role',
                'full_name'
            ]);
    }

    public function testUpdateUsers() {
        $post = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/users/$post->id", $data);

        $response->assertStatus(200)
        ->assertJson($data);
    }

    public function testUpdateUsersWithNoId() {
        $post = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/users/", $data);

        $response->assertStatus(405);
    }

    public function testUpdateUsersWithNonexistentId() {
        $post = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/users/208084082084", $data);

        $response->assertStatus(404);
    }

    public function testUpdateUsersWithNoData() {
        $post = factory(User::class)->create();
        $data = [];

        $response = $this->json('PUT', "/api/v1/users/", $data);

        $response->assertStatus(405);
    }

    public function testDeleteUsers() {
        $post = factory(User::class)->create();

        $response = $this->json('DELETE', "/api/v1/users/$post->id");

        $response->assertStatus(200);
    }

    public function testDeleteUsersWithNoId() {
        $post = factory(User::class)->create();

        $response = $this->json('DELETE', "/api/v1/users/");

        $response->assertStatus(405);
    }

    public function testDeleteUsersWithNonexistentId() {
        $post = factory(User::class)->create();

        $response = $this->json('DELETE', "/api/v1/users/0280398420384");

        $response->assertStatus(404);
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

        $response = $this->json('GET', "/api/v1/users");

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
