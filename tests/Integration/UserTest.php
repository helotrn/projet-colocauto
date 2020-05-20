<?php

namespace Tests\Integration;

use App\Models\User;
use App\Models\Community;
use Tests\TestCase;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    private static $getUserResponseStructure = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'description',
        'date_of_birth',
        'address',
        'postal_code',
        'phone',
        'is_smart_phone',
        'other_phone',
    ];

    private static $getCommunityResponseStructure = [
        'id',
        'name',
        'description',
        'area',
        'created_at',
        'updated_at',
        'deleted_at',
        'type',
        'center',
    ];

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
        $user = factory(User::class)->create();
        $data = [];

        $response = $this->json('GET', "/api/v1/users/$user->id", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'last_name',
                'email',
                'email_verified_at',
                'description',
                'date_of_birth',
                'address',
                'postal_code',
                'phone',
                'is_smart_phone',
                'other_phone',
                'remember_token',
                'created_at',
                'updated_at',
                'role',
                'full_name'
            ]);
    }

    public function testUpdateUsers() {
        $user = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/users/$user->id", $data);

        $response->assertStatus(200)
            ->assertJson($data);
    }

    public function testUpdateUsersWithNonexistentId() {
        $user = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/users/208084082084", $data);

        $response->assertStatus(404);
    }

    public function testDeleteUsers() {
        $user = factory(User::class)->create();

        $response = $this->json('DELETE', "/api/v1/users/$user->id");

        $response->assertStatus(200);
    }


    public function testDeleteUsersWithNonexistentId() {
        $user = factory(User::class)->create();

        $response = $this->json('DELETE', "/api/v1/users/0280398420384");

        $response->assertStatus(404);
    }


    public function testListUsers() {
        $users = factory(User::class, 2)->create()->map(function ($user) {
            return $user->only(static::$getUserResponseStructure);
        });

        $response = $this->json('GET', "/api/v1/users");

        $response->assertStatus(200)
            ->assertJsonStructure($this->buildCollectionStructure(static::$getUserResponseStructure));
    }

    public function testAssociateUserToCommunity() {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $response = $this->json('PUT', "/api/v1/users/$user->id/communities/$community->id");
        $response->assertStatus(200);
    }

    public function testUpdateUserWithCommunity() {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $data = [
            'communities' => [['id' => $community->id]]
        ];

        $response = $this->json('PUT', "/api/v1/users/$user->id", $data);
        $response->assertStatus(200)->assertJsonStructure(static::$getUserResponseStructure);
    }

    public function testShowUsersCommunities() {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $otherCommunity = factory(Community::class)->create();

        $data = [
            'communities' => [['id' => $community->id]]
        ];
        $response = $this->json('PUT', "/api/v1/users/$user->id", $data);
        $response->assertStatus(200);

        $data = [
            'users.id' => $user->id,
        ];

        $response = $this->json('GET', "/api/v1/communities/$otherCommunity->id", $data);
        $response->assertStatus(404);

        $response = $this->json('GET', "/api/v1/communities/$community->id", $data);
        $response->assertStatus(200);
    }

    public function testListUsersCommunities() {
        $user = factory(User::class)->create();
        $communities = factory(Community::class, 2)->create();

        $data = [
            'communities' => [ ['id' => $communities[0]->id] ]
        ];
        $response = $this->json('PUT', "/api/v1/users/$user->id", $data);

        $data = [
            'users.id' => $user->id,
        ];
        $response = $this->json('GET', '/api/v1/communities', $data);
        $response->assertStatus(200)
            ->assertJson([ 'total' => 1 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getCommunityResponseStructure));
    }
}
