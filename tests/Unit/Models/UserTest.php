<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Noke;
use Stripe;
use Tests\TestCase;

class UserTest extends TestCase
{
    public $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new User();
    }

    public function testUpdateBalance()
    {
        $user = factory(User::class)->create();

        $this->assertEquals(0, $user->balance);

        $user->addToBalance(10.1);
        $this->assertEquals(10.1, $user->balance);

        $user->updateBalance(-2.8);
        $this->assertEquals(7.3, $user->balance);

        $user->updateBalance(5);
        $this->assertEquals(12.3, $user->balance);

        $user->removeFromBalance(10);
        $this->assertEquals(2.3, $user->balance);
    }

    public function testRemoveFromBalanceBelowZero()
    {
        $user = factory(User::class)->create([
            "balance" => 1,
        ]);

        $this->assertEquals(1, $user->balance);
        $user->removeFromBalance(1);

        $this->assertEquals(0, $user->balance);

        $user->balance = 1;
        $user->save();

        $this->assertEquals(1, $user->balance);

        // If the balance is not sufficient, abort
        $this->expectException(
            "Symfony\Component\HttpKernel\Exception\HttpException"
        );
        $user->removeFromBalance(1.01);
        $this->assertEquals(0, 1); // Raised above
    }

    public function testUpdateUserEmailFromModelDirectly()
    {
        $user = factory(User::class)->create([
            "email" => "original@user.email",
        ]);

        $originalEmail = $user->email;
        $changedEmail = "changed@email.com";

        Noke::shouldReceive("findUserByEmail")
            ->withArgs(function ($a, $b) use ($originalEmail) {
                return $a === $originalEmail && $b === true;
            })
            ->andReturns(
                (object) [
                    "username" => $originalEmail,
                ]
            )
            ->once();

        Noke::shouldReceive("updateUser")
            ->withArgs(function ($arg) use ($changedEmail) {
                return $arg->username === $changedEmail;
            })
            ->once();

        $user->email = $changedEmail;
        $user->save();
    }

    public function testUserGetNokeUser()
    {
        $user = factory(User::class)->create();

        Noke::shouldReceive("findOrCreateUser")->once();

        $user->getNokeUser();
    }

    public function testUserStripeCustomerMethod()
    {
        $user = factory(User::class)->create();

        Stripe::shouldReceive("getUserCustomer")
            ->once()
            ->with($user);

        $user->getStripeCustomer();
    }

    public function testUpdateEmailSuccess() {
        $newUser = $this->createTestUser();
        $this->actingAs($newUser);

        $this->assertEquals("test@locomotion.app", $newUser->email);

        $data = [
            "email" => "test_changed@locomotion.app",
            "password" => "locomotion",
        ];

        $response = $this->json("POST", "/api/v1/users/$newUser->id/email", $data);
        $json = $response->json();

        $response->assertStatus(200);
        $this->assertEquals("test_changed@locomotion.app", array_get($json, "email"));
    }

    public function testUpdateEmailError() {
        $newUser = $this->createTestUser();
        $this->actingAs($newUser);

        $this->assertEquals("test@locomotion.app", $newUser->email);

        $data = [
            "email" => "test_changed@locomotion.app",
            "password" => "wrongpassword",
        ];

        $response = $this->json("POST", "/api/v1/users/$newUser->id/email", $data);
        $json = $response->json();

        $response->assertStatus(401);
        $this->assertEquals("test@locomotion.app", array_get($json, "email"));
    }

    public function testUpdatePasswordSuccess() {
        $newUser = $this->createTestUser();
        $this->actingAs($newUser);

        $this->assertTrue(Hash::check("locomotion", $newUser->password));

        $data = [
            "current" => "locomotion",
            "new" => "newpassword",
        ];

        $response = $this->json("POST", "/api/v1/users/$newUser->id/password", $data);
        $password = User::find($newUser->id)->password;

        $response->assertStatus(200);

        $this->assertTrue(Hash::check("newpassword", $password));
        $this->assertFalse(Hash::check("locomotion", $password));
    }

    public function testUpdatePasswordError() {
        $newUser = $this->createTestUser();
        $this->actingAs($newUser);

        $this->assertTrue(Hash::check("locomotion", $newUser->password));

        $data = [
            "current" => "wrongpassword",
            "new" => "newpassword",
        ];

        $response = $this->json("POST", "/api/v1/users/$newUser->id/password", $data);
        $password = User::find($newUser->id)->password;

        $response->assertStatus(401);
        $this->assertTrue(Hash::check("locomotion", $password));      
        $this->assertFalse(Hash::check("newpassword", $password));
    }

    private function createTestUser()
    {
        $user = factory(User::class)->create([
            "email" => "test@locomotion.app",
            "password" => Hash::make("locomotion"),
            "role" => null,
        ]);

        return $user;
    }
}
