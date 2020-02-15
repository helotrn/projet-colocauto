<?php

namespace Tests\Integration;

use App\Models\Owner;
use App\Models\User;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    private static $getOwnerResponseStructure = [
        'id',
        'submitted_at',
        'approved_at',
    ];

    public function testCreateOwners() {
        $data = [
            'submitted_at' => now()->toDateTimeString(),
            'approved_at' => now()->toDateTimeString(),
            'user_id' => $this->user->id,
        ];

        $response = $this->json('POST', "/api/v1/owners", $data);

        $response->assertStatus(201)
            ->assertJson(['user_id' => $this->user->id])
            ->assertJsonStructure(static::$getOwnerResponseStructure);
    }

    public function testShowOwners() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);

        $response = $this->json('GET', "/api/v1/owners/$owner->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $owner->id])
            ->assertJsonStructure(static::$getOwnerResponseStructure);
    }

    public function testUpdateOwners() {
        $this->markTestIncomplete();
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $approvedAt = now()->toIsoString();
        $data = [
            'approved_at' => $approvedAt,
        ];

        $response = $this->json('PUT', "/api/v1/owners/$owner->id", $data);
        //TODO fix date formatting
        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteOwners() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);

        $response = $this->json('DELETE', "/api/v1/owners/$owner->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/owners/$owner->id");
        $response->assertStatus(404);
    }

    public function testListOwners() {
        $owners = factory(Owner::class, 2)->create(['user_id' => $this->user->id])->map(function ($owner) {
            return $owner->only(static::$getOwnerResponseStructure);
        });

        $response = $this->json('GET', "/api/v1/owners");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getOwnerResponseStructure));
    }
}
