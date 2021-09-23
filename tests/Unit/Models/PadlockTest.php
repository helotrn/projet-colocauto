<?php

namespace Tests\Unit\Models;

use App\Models\Padlock;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PadlockTest extends TestCase
{
    public function testPadlockUniqueMacAddress()
    {
        $padlock = factory(Padlock::class)->create();

        try {
            factory(Padlock::class)->create([
                "mac_address" => "00:11:22:33:44:ZZ", // Invalid but fine for tests purposes
            ]);
        } catch (\Throwable $e) {
            $this->assertEquals(
                1,
                0,
                "Creating a padlock with an unique mac_address should work"
            );
        }

        $duplicatePadlock = factory(Padlock::class)->make([
            "mac_address" => strtolower($padlock->mac_address),
        ]);

        $validator = Validator::make(
            $duplicatePadlock->toArray(),
            Padlock::$rules
        );
        $this->assertTrue($validator->fails());

        // Protected on database
        $this->expectException(\PDOException::class);
        $duplicatePadlock->save();
    }
}
