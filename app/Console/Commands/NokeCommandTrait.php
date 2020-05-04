<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Cache;

trait NokeCommandTrait
{
    protected $baseUrl = 'https://v1-api-nokepro.appspot.com';
    protected $token;

    protected $groups = [];
    protected $groupsIndex = [];

    protected $users = [];
    protected $usersIndex = [];

    protected function login() {
        if ($this->token) {
            return $this->token;
        }

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

    protected function getLocks($force = false) {
        if ($force) {
            Cache::forget('noke:locks');
        }

        if ($locks = Cache::get('noke:locks')) {
            return json_decode($locks);
        }

        $locksResponse = $this->client->post("{$this->baseUrl}/lock/get/list/", [
            'json' => [
                'page' => 1,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $this->token",
            ],
        ]);

        $locksResult = json_decode($locksResponse->getBody());

        Cache::put('noke:locks', json_encode($locksResult->data), 3600);

        return $locksResult->data;
    }

    protected function getGroups($force = false) {
        if ($force) {
            Cache::forget('noke:groups');
        }

        if ($groupsJson = Cache::get('noke:groups')) {
            $this->groups = json_decode($groupsJson);
        } else {
            $page = 0;
            do {
                $page += 1;

                $groupsResponse = $this->client->post("{$this->baseUrl}/group/get/all/", [
                    'json' => [
                        'page' => $page,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $this->token",
                    ],
                ]);
                $groupsResult = json_decode($groupsResponse->getBody());

                $this->groups = array_merge($groupsResult->data->groups, $this->groups);
                $maxPage = intval($groupsResult->data->pageCount);
            } while ($maxPage > $page);

            Cache::set('noke:groups', json_encode($this->groups), 3600);
        }

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }
    }

    protected function getUsers($force = false) {
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

            Cache::set('noke:users', json_encode($this->users), 3600);
        }

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }
    }

    protected function getGroupProfile($id) {
        $response = $this->client->post("{$this->baseUrl}/group/profile/", [
            'json' => [
                'id' => $id,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $this->token",
            ],
        ]);

        $result = json_decode($response->getBody());

        return $result->data;
    }
}
