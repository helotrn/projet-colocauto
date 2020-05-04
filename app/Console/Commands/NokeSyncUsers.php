<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncUsers extends Command
{
    private $baseUrl = 'https://v1-api-nokepro.appspot.com';
    private $token;
    private $users = [];
    private $usersIndex = [];

    protected $signature = 'noke:sync:users';

    protected $description = 'Synchronize NOKE users';

    public function __construct(Client $client) {
        parent::__construct();

        $this->client = $client;
    }

    public function handle() {
        $this->info('Logging in...');
        $this->login();

        $this->info('Fetching users...');
        $this->getUsers();

        $this->info('Creating remote users...');
        $this->createUsers();

        $this->info('Done.');
    }

    private function login() {
        $loginResponse = $this->client->post("{$this->baseUrl}/company/web/login/", [
            'json' => [
                'username' => config('services.noke.username'),
                'password' => config('services.noke.password'),
            ],
        ]);

        $loginResult = json_decode($loginResponse->getBody());

        if ($loginResult->result === 'failure') {
            throw new \Exception('login error');
        }

        $this->token = $loginResult->token;
    }

    private function getUsers() {
          $usersResponse = $this->client->post("{$this->baseUrl}/user/get/list/", [
              'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => "Bearer $this->token",
              ],
          ]);
          $usersResults = json_decode($usersResponse->getBody());
          $this->users = $usersResults->data;

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }
    }

    private function createUsers() {
        $users = User::whereHas('borrower')
            ->whereNotNull('submitted_at')
            ->select('id', 'email', 'name', 'last_name', 'phone', 'is_smart_phone')
            ->get()
            ->toArray();

        foreach ($users as $user) {
            if (!isset($this->usersIndex[$user['email']])) {
                $this->warn("Creating user {$user['email']}.");

                $data = [
                    'city' => '',
                    'company' => '',
                    'mobilePhone' => $user['is_smart_phone'] ? $user['phone'] : '',
                    'name' => "{$user['name']} {$user['last_name']}",
                    'notifyEmail' => 1,
                    'notifySMS' => $user['is_smart_phone'] ? 1 : 0,
                    'permissions' => ['app_flag'],
                    'phoneCountryCode' => '1',
                    'state' => '',
                    'streetAddress' => '',
                    'title' => '',
                    'username' => $user['email'],
                    'zip' => '',
                ];

                $this->client->post(
                    "{$this->baseUrl}/user/create/",
                    [
                        'json' => $data,
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => "Bearer $this->token",
                        ],
                    ]
                );
            }
        }
    }
}
