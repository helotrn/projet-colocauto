<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NokeService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;

class NokeSyncUsers extends Command
{
    protected $signature = 'noke:sync:users
                            {--pretend : Do not call remote API}';

    protected $description = "Synchronize NOKE users";

    private $pretend = false;

    public function __construct(Client $client, NokeService $service)
    {
        parent::__construct();

        $this->client = $client;
        $this->service = $service;
    }

    public function handle()
    {
        // if ($this->option("pretend")) {
        //     $this->pretend = true;
        // }

        Log::info("Fetching users...");
        $this->getUsers(true);

        Log::info("Creating remote users...");
        $this->createUsers();

        Log::info("Done.");
    }

    private function getUsers()
    {
        $this->users = $this->service->fetchUsers(true);

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }
    }

    private function createUsers()
    {
        $users = User::whereHas("borrower")
            ->whereHas("approvedCommunities")
            ->whereNotNull("submitted_at")
            ->select(
                "id",
                "email",
                "name",
                "last_name",
                "phone",
                "is_smart_phone"
            )
            ->get();

        foreach ($users as $user) {
            if (!isset($this->usersIndex[$user->email])) {
                Log::info("Creating user {$user->email}.");

                if ($this->pretend) {
                    continue;
                }

                $this->service->findOrCreateUser($user);
            }
        }
    }
}
