<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class NokeService
{
    private $baseUrl = 'https://v1-api-nokepro.appspot.com';
    private $token;

    private $users = [];
    private $usersIndex = [];

    private $client;

    public function __construct(Client $client) {
        $this->client = $client;

        $this->token = Cache::get('noke:token');

        if (!$this->token) {
            $this->resetToken();
        }
    }

    public function findOrCreateUser(User $user) {
        if (!$this->users) {
            $this->fetchUsers();
        }

        if (isset($this->usersIndex[$user->email])) {
            return $this->usersIndex[$user->email];
        }

        $data = [
            'city' => '',
            'company' => '',
            'mobilePhone' => $user->is_smart_phone ? $user->phone : '',
            'name' => "{$user->name} {$user->last_name}",
            'notifyEmail' => 1,
            'notifySMS' => $user->is_smart_phone ? 1 : 0,
            'permissions' => ['app_flag'],
            'phoneCountryCode' => '1',
            'state' => '',
            'streetAddress' => '',
            'title' => '',
            'username' => $user->email,
            'zip' => '',
        ];

        $response = $this->client->post(
            "{$this->baseUrl}/user/create/",
            [
                'json' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer $this->token",
                ],
            ]
        );

        $result = json_decode($response->getBody());

        if (isset($result->result) && $result->result === 'failure') {
            return null;
        }

        $newUser = $result->data;
        $this->usersIndex[$user->email] = $newUser;
        array_push($this->users, $newUser);

        Cache::set('noke:users', json_encode($this->users), 14400);

        return $newUser;
    }

    public function fetchUsers($force = false) {
        if ($force) {
            Cache::forget('noke:users');
        }

        if ($users = Cache::get('noke:users')) {
            $this->users = json_decode($users);
        } else {
            $usersResponse = $this->client->post("{$this->baseUrl}/user/get/list/", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer $this->token",
                ],
            ]);
            $usersResults = json_decode($usersResponse->getBody());

            $this->users = $usersResults->data;

            Cache::set('noke:users', json_encode($this->users), 14400);
        }

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }

        return $this->users;
    }

    private function resetToken() {
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

        Cache::set('noke:token', $this->token, 3600);
    }
}
