<?php

namespace Tests\Integration;

use App\Models\Padlock;
use Tests\TestCase;

class PadlockTest extends TestCase
{
    private static $getPadlocksResponseStructure = [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];

    public function testShowPadlocks() {
        $padlock = factory(Padlock::class)->create();

        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id));

        $response->assertStatus(200)->assertJson($padlock->toArray());
    }

    public function testOrderPadlocksById() {
        $data = [
          'order' => 'id',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,loanable.name',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testOrderPadlocksByName() {
        $data = [
          'order' => 'name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,loanable.name',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testOrderPadlocksByMacAddress() {
        $data = [
          'order' => 'mac_address',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,loanable.name',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testOrderPadlocksByLoanableName() {
        $data = [
          'order' => 'loanable.name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,loanable.name',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testFilterPadlocksByExternalId() {
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'external_id' => '20345',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testFilterPadlocksByName() {
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'name' => 'Cadenas',
        ];
        $response = $this->json('GET', "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testFilterPadlocksByLoanableId() {
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'loanable.id' => '4',
        ];
        $response = $this->json('GET', "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testFilterPadlocksByLoanableName() {
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'loanable.name' => 'Auto',
        ];
        $response = $this->json('GET', "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testFilterPadlocksByDeletedAt() {
                             // Lower bound only
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'deleted_at' => '2020-11-10T01:23:45Z@',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;

                             // Lower and upper bounds
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'deleted_at' => '2020-11-10T01:23:45Z@2020-11-12T01:23:45Z',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;

                             // Upper bound only
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'deleted_at' => '@2020-11-12T01:23:45Z',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;

                             // Degenerate case when bounds are removed
        $data = [
          'page' => 1,
          'per_page' => 10,
          'fields' => 'id,name,last_name,full_name,email',
          'deleted_at' => '@',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;
    }

    public function testSearchPadlocks() {
                             // Test searching for a padlock that does not exist.
        $data = [
          'fields' => 'id,name',
          '!loanable' => '1',
          'q' => 'Test padlock',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
            ;


                             // Test searching for a padlock that does exist.
        $padlock_data = [
            'name' => 'Test padlock to find',
            'mac_address' => $this->faker->macAddress,
        ];
        $padlock = factory(Padlock::class)->create($padlock_data);

        $data = [
          'fields' => 'id,name',
          '!loanable' => '1',
          'q' => 'Test padlock',
        ];
        $response = $this->json('GET', "/api/v1/padlocks/", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getPadlocksResponseStructure)
                             // Assert that one padlock is returned.
            ->assertJson([ 'total' => 1 ])
            ;
    }

    public function testUpdatePadlocks() {
        $padlock = factory(Padlock::class)->create();
        $data = [
            'mac_address' => $this->faker->macAddress,
        ];
        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id))
            ->assertJson([ 'mac_address' => $padlock->mac_address ]);

        $response = $this->json('PUT', route('padlocks.update', $padlock->id), $data);

        $response->assertStatus(200)->assertJson($data);

        $response = $this->json('GET', route('padlocks.retrieve', $padlock->id))
            ->assertJson($data);
    }

    public function testListPadlocks() {
        $padlocks = factory(Padlock::class, 2)->create()->map(function ($padlock) {
            return $padlock->only(['id', 'mac_address']);
        });

        $response = $this->json('GET', route('padlocks.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => $padlocks->toArray()
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'mac_address',
                    ],
                ],
            ]);
    }
}
