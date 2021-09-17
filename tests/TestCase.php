<?php

namespace Tests;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;
use Noke;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    public static $validationErrorStructure = ["message", "errors" => []];

    public static $collectionResponseStructure = [
        "current_page",
        "data",
        "first_page_url",
        "from",
        "last_page",
        "last_page_url",
        "next_page_url",
        "path",
        "per_page",
        "prev_page_url",
        "to",
        "total",
    ];

    protected $faker;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->user = factory(User::class)->make([
            "role" => "admin",
        ]);
        $this->user->save();

        $this->actAs($this->user);

        \DB::statement(
            <<<SQL
INSERT INTO oauth_clients
(id, name, secret, redirect, personal_access_client, password_client, revoked,
    created_at, updated_at)
VALUES (
    'pTrmRvquBD6mXlep',
    'LocoMotion Password Grant Client',
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

        Carbon::setTestNow();
    }

    protected function buildCollectionStructure(array $template)
    {
        return [
            "current_page",
            "data" => [
                "*" => $template,
            ],
            "first_page_url",
            "from",
            "last_page",
            "last_page_url",
            "next_page_url",
            "path",
            "per_page",
            "prev_page_url",
            "to",
            "total",
        ];
    }

    protected function actAs($user)
    {
        Passport::actingAs($user);
    }
}
