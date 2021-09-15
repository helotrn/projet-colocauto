<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\NokeSyncLocks as NokeSyncLocksCommand;
use App\Models\Padlock;
use App\Services\NokeService;
use GuzzleHttp\Client;
use Mockery;
use Tests\TestCase;

class NokeSyncLocksTest extends TestCase
{
    public function testNokeSyncLocksCommandPadlockAndRemoteGroupCreation()
    {
        $this->instance(
            NokeService::class,
            Mockery::mock(NokeService::class, function ($mock) {
                $mock
                    ->shouldReceive("fetchLocks")
                    ->once()
                    ->with(true)
                    ->andReturn([
                        (object) [
                            "id" => 1,
                            "name" => "Test",
                            "macAddress" => "AA:BB:CC:DD:EE:FF",
                        ],
                        (object) [
                            "id" => 2,
                            "name" => "Other",
                            "macAddress" => "11:22:33:44:55:66",
                        ],
                    ]);
                $mock
                    ->shouldReceive("fetchGroups")
                    ->once()
                    ->with(true)
                    ->andReturn([]);

                $mock
                    ->shouldReceive("findOrCreateGroup")
                    ->once()
                    ->with("API AA:BB:CC:DD:EE:FF", 1);
                $mock
                    ->shouldReceive("findOrCreateGroup")
                    ->once()
                    ->with("API 11:22:33:44:55:66", 2);
            })
        );

        $this->artisan("noke:sync:locks");

        $this->assertEquals(2, Padlock::count());
    }

    public function testNokeSyncLocksCommandDoNothingCase()
    {
        factory(Padlock::class)->create([
            "name" => "Test",
            "mac_address" => "AA:BB:CC:DD:EE:FF",
            "external_id" => "1",
        ]);
        factory(Padlock::class)->create([
            "name" => "Other",
            "mac_address" => "11:22:33:44:55:66",
            "external_id" => "2",
        ]);

        $this->instance(
            NokeService::class,
            Mockery::mock(NokeService::class, function ($mock) {
                $mock
                    ->shouldReceive("fetchLocks")
                    ->once()
                    ->with(true)
                    ->andReturn([
                        (object) [
                            "id" => 1,
                            "name" => "Test",
                            "macAddress" => "AA:BB:CC:DD:EE:FF",
                        ],
                        (object) [
                            "id" => 2,
                            "name" => "Other",
                            "macAddress" => "11:22:33:44:55:66",
                        ],
                    ]);
                $mock
                    ->shouldReceive("fetchGroups")
                    ->once()
                    ->with(true)
                    ->andReturn([
                        (object) [
                            "id" => 1,
                            "name" => "API AA:BB:CC:DD:EE:FF",
                        ],
                        (object) [
                            "id" => 2,
                            "name" => "API 11:22:33:44:55:66",
                        ],
                    ]);
            })
        );

        $this->artisan("noke:sync:locks");
    }

    public function testNokeSyncLocksCommandRemoveLocalPadlocks()
    {
        factory(Padlock::class)->create([
            "name" => "Test",
            "mac_address" => "AA:BB:CC:DD:EE:FF",
            "external_id" => "1",
        ]);
        factory(Padlock::class)->create([
            "name" => "Other",
            "mac_address" => "11:22:33:44:55:66",
            "external_id" => "2",
        ]);

        $this->assertEquals(2, Padlock::count());

        $this->instance(
            NokeService::class,
            Mockery::mock(NokeService::class, function ($mock) {
                $mock
                    ->shouldReceive("fetchLocks")
                    ->once()
                    ->with(true)
                    ->andReturn([
                        (object) [
                            "id" => 1,
                            "name" => "Test",
                            "macAddress" => "AA:BB:CC:DD:EE:FF",
                        ],
                    ]);
                $mock
                    ->shouldReceive("fetchGroups")
                    ->once()
                    ->with(true)
                    ->andReturn([
                        (object) [
                            "id" => 1,
                            "name" => "API AA:BB:CC:DD:EE:FF",
                        ],
                        (object) [
                            "id" => 2,
                            "name" => "API 11:22:33:44:55:66",
                        ],
                    ]);
            })
        );

        $this->artisan("noke:sync:locks");

        $this->assertEquals(1, Padlock::count());
    }
}
