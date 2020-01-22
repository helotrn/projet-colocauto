<?php

namespace Tests;

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    public static $validationErrorStructure = [
        'message',
        'errors' => []
    ];

    protected $faker;
    protected $user;

    public function setUp(): void {
        parent::setUp();

        $this->user = factory(User::class)->create();

        Passport::actingAs($this->user);
        $this->faker = Factory::create();

        \DB::statement(<<<SQL
INSERT INTO oauth_clients
(id, name, secret, redirect, personal_access_client, password_client, revoked,
    created_at, updated_at)
VALUES (
    'pTrmRvquBD6mXlep',
    'Locomotion Password Grant Client',
    '1hu8xmgtXI8O6HRH4zIbRt92X6rSLCgY6NtTNyxN',
    'http://localhost',
    false,
    true,
    false,
    current_timestamp,
    current_timestamp
)
SQL
        );
    }
}
