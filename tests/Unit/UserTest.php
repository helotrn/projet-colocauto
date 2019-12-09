<?php
namespace Tests\Unit;

use App\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testCreateUsers() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
        $this->post(route('users.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateUsers() {
        $post = factory(User::class)->create();
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        $this->put(route('users.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowUsers() {
        $post = factory(User::class)->create();
        $this->get(route('users.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteUsers() {
        $post = factory(User::class)->create();
        $this->delete(route('users.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListUsers() {
        $users = factory(User::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'email',
                'email_verified_at',
            ]);
        });
        $this->get(route('users'))
            ->assertStatus(200)
            ->assertJson($users->toArray())
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                ],
            ]);
    }
}
