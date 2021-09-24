<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Noke;
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
}
