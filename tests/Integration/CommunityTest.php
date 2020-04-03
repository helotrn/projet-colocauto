<?php

namespace Tests\Integration;

use App\Models\Community;
use App\Models\User;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    private static $getUserResponseStructure = [
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

    public function testCreateCommunities() {
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'area' => null,
        ];

        $response = $this->json('POST', "/api/v1/communities/", $data);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'area',
                'updated_at',
                'created_at'
            ]);
    }

    public function testCreateCommunitiesWithNullName() {
        $data = [
            'name' => null,
            'description' => $this->faker->sentence,
            'area' => null,
        ];

        $response = $this->json('POST', "/api/v1/communities/", $data);
        $response->assertStatus(422);
    }

    public function testCreateCommunitiesWithInvalidType() {
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'area' => null,
            'type' => 'something',
        ];

        $response = $this->json('POST', "/api/v1/communities/", $data);
        $response->assertStatus(422);
    }

    public function testShowCommunities() {
        $community = factory(Community::class)->create();

        $response = $this->json('GET', "/api/v1/communities/$community->id", []);
        $response->assertStatus(200);
    }

    public function testUpdateCommunities() {
        $community = factory(Community::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/communities/$community->id", $data);
        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateCommunitiesByAdminOfCommunity() {
        $community = factory(Community::class)->create();

        $this->user->role = '';
        $this->user->save();

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('communities.update', $community->id), $data);
        $response->assertStatus(403);

        $communities = [
            $community->id => [
                'role' => 'admin',
            ],
        ];
        $this->user->communities()->sync($communities);

        $response = $this->json('PUT', route('communities.update', $community->id), $data);
        $response->assertStatus(200);
    }

    public function testUpdateCommunitiesByNonAdmin() {
        $this->user->role = "";
        $community = factory(Community::class)->create();
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', route('communities.update', $community->id), $data);

        $response->assertStatus(403);
    }

    public function testDeleteCommunities() {
        $community = factory(Community::class)->create();

        $response = $this->json('DELETE', route('communities.destroy', $community->id));

        $response->assertStatus(200);
    }

    public function testDeleteCommunitiesByNonAdmin() {
        $this->user->role = "";
        $community = factory(Community::class)->create();

        $response = $this->json('DELETE', route('communities.destroy', $community->id));

        $response->assertStatus(403);
    }

    public function testListCommunities() {
        $communities = factory(Community::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'name',
                'description',
                'area',
            ]);
        });

        $response = $this->json('GET', "/api/v1/communities", []);
        $response->assertStatus(200)
            ->assertJsonStructure($this->buildCollectionStructure([
                'id', 'name', 'description', 'area',
            ]));
    }

    public function testShowCommunitiesUsers() {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $data = [
            'users' => [['id' => $user['id']]]
        ];
        $response = $this->json('PUT', "/api/v1/communities/$community->id", $data);

        $data = [
            'community.id' => $community->id,
        ];
        $response = $this->json('GET', '/api/v1/users', $data);
        $response->assertStatus(200)
            ->assertJsonStructure($this->buildCollectionStructure(static::$getUserResponseStructure));
    }
}
