<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NokeService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncUsers extends Command
{
    use NokeCommandTrait;

    protected $signature = 'noke:sync:users';

    protected $description = 'Synchronize NOKE users';

    public function __construct(Client $client, NokeService $service) {
        parent::__construct();

        $this->client = $client;
        $this->service = $service;
    }

    public function handle() {
        $this->info('Fetching users...');
        $this->getUsers(true);

        $this->info('Creating remote users...');
        $this->createUsers();

        $this->info('Done.');
    }

    private function createUsers() {
        $users = User::whereHas('borrower')
            ->whereNotNull('submitted_at')
            ->select('id', 'email', 'name', 'last_name', 'phone', 'is_smart_phone')
            ->get();

        foreach ($users as $user) {
            if (!isset($this->usersIndex[$user->email])) {
                $this->warn("Creating user {$user->email}.");

                $this->service->findOrCreateUser($user);
            }
        }
    }
}
