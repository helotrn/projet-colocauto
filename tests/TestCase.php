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

    protected $faker;
    protected $user;

    public function setUp(): void {
        parent::setUp();

        $this->user = factory(User::class)->create();

        Passport::actingAs($this->user);
        $this->faker = Factory::create();
    }
}
