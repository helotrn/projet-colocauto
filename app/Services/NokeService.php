<?php

namespace App\Services;

use App\Models\User;
use ErrorException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NokeService
{
    private $baseUrl = "https://v1-api-nokepro.appspot.com";
    private $token;

    private $groups = [];
    private $groupsIndex = [];

    private $locks = [];
    private $locksIndex = [];

    private $users = [];
    private $usersIndex = [];

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->token = Cache::get("noke:token");

        if (!$this->token && app()->environment() === "production") {
            $this->resetToken();
        }
    }

    public function findOrCreateGroup($groupName, $lockExternalId)
    {
        if (!$this->groups) {
            $this->fetchGroups();
        }

        if (isset($this->groupsIndex[$groupName])) {
            return $this->groupsIndex[$groupName];
        }

        $url = "{$this->baseUrl}/group/create/";
        Log::info("Request to $url for group $groupName");

        if (app()->environment() === "production") {
            $response = $this->client->post($url, [
                "json" => [
                    "name" => $groupName,
                    "groupType" => "online",
                    "lockIds" => [intval($lockExternalId)],
                    "userIds" => [intval(config("services.noke.api_user_id"))],
                    "schedule" => [
                        [
                            "startDate" => "2020-05-01T00:00:00-04:00",
                            "endDate" => "2030-05-01T23:59:59-04:00",
                            "expiration" => "2030-05-01T23:59:59-04:00",
                            "repeatType" => "none",
                            "dayOfWeek" => "",
                            "name" => strval(time()),
                        ],
                    ],
                ],
                "headers" => [
                    "Accept" => "application/json",
                    "Authorization" => "Bearer $this->token",
                ],
            ]);

            $result = json_decode($response->getBody());

            if (isset($result->result) && $result->result === "failure") {
                return null;
            }

            $newGroup = $result->data;
            $this->groupsIndex[$groupName] = $newGroup;
            array_push($this->groups, $newGroup);

            Cache::set("noke:groups", json_encode($this->groups), 14400);

            return $newGroup;
        }
    }

    public function findUserByEmail($email, $force = false)
    {
        $this->fetchUsers($force);

        if (!isset($this->usersIndex[$email])) {
            return null;
        }

        $user = $this->usersIndex[$email];

        $url = "{$this->baseUrl}/user/profile/";
        Log::info("Request to $url for user ID $user->id");

        $response = $this->client->post($url, [
            "json" => [
                "id" => $user->id,
            ],
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer $this->token",
            ],
        ]);

        $result = json_decode($response->getBody());

        if (isset($result->result) && $result->result === "failure") {
            return null;
        }

        return $result->data;
    }

    public function findOrCreateUser(User $user)
    {
        if (!$this->users) {
            $this->fetchUsers();
        }

        if (isset($this->usersIndex[$user->email])) {
            return $this->usersIndex[$user->email];
        }

        $data = [
            "city" => "",
            "company" => "",
            "mobilePhone" => $user->is_smart_phone ? $user->phone : "",
            "name" => $user->full_name,
            "notifyEmail" => 1,
            "notifySMS" => $user->is_smart_phone ? 1 : 0,
            "permissions" => ["app_flag"],
            "phoneCountryCode" => "1",
            "state" => "",
            "streetAddress" => "",
            "title" => "",
            "username" => $user->email,
            "zip" => "",
        ];

        $url = "{$this->baseUrl}/user/create/";
        Log::info("Request to $url for user ID $user->id");

        if (app()->environment() == "production") {
            $response = $this->client->post($url, [
                "json" => $data,
                "headers" => [
                    "Accept" => "application/json",
                    "Authorization" => "Bearer $this->token",
                ],
            ]);

            $result = json_decode($response->getBody());

            if (isset($result->result) && $result->result === "failure") {
                return null;
            }

            $newUser = $result->data;
            $this->usersIndex[$user->email] = $newUser;
            array_push($this->users, $newUser);

            Cache::set("noke:users", json_encode($this->users), 14400);

            return $newUser;
        }
    }

    public function fetchGroups($force = false)
    {
        if ($force) {
            Cache::forget("noke:groups");
        }

        if ($groupsJson = Cache::get("noke:groups")) {
            $this->groups = json_decode($groupsJson);
        } else {
            $page = 0;
            do {
                $page += 1;

                $url = "{$this->baseUrl}/group/get/all/";
                Log::info("Request to $url with page $page");

                $groupsResponse = $this->client->post($url, [
                    "json" => [
                        "page" => $page,
                    ],
                    "headers" => [
                        "Accept" => "application/json",
                        "Authorization" => "Bearer $this->token",
                    ],
                ]);
                $groupsResult = json_decode($groupsResponse->getBody());

                $this->groups = array_merge(
                    $groupsResult->data->groups,
                    $this->groups
                );
                $maxPage = intval($groupsResult->data->pageCount);
            } while ($maxPage > $page);

            Cache::set("noke:groups", json_encode($this->groups), 14400);
        }

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }

        return $this->groups;
    }

    public function fetchLocks($force = false)
    {
        if ($force) {
            Cache::forget("noke:locks");
        }

        if ($locks = Cache::get("noke:locks")) {
            return json_decode($locks);
        }

        $url = "{$this->baseUrl}/lock/get/list/";
        Log::info("Request to $url");

        $locksResponse = $this->client->post($url, [
            "json" => [
                "page" => 1,
            ],
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer $this->token",
            ],
        ]);

        $locksResult = json_decode($locksResponse->getBody());

        Cache::put("noke:locks", json_encode($locksResult->data), 14400);

        $this->locks = $locksResult->data;

        foreach ($this->locks as $lock) {
            $this->locksIndex[$lock->macAddress] = $lock;
            $this->locksIndex[$lock->macAddress]->users = [];
        }

        return $this->locks;
    }

    public function fetchUsers($force = false)
    {
        if ($force) {
            Cache::forget("noke:users");
        }

        if ($users = Cache::get("noke:users")) {
            $this->users = json_decode($users);
        } else {
            if (app()->environment() === "production") {
                $url = "{$this->baseUrl}/user/get/list/";
                Log::info("Request to $url");

                $usersResponse = $this->client->post($url, [
                    "headers" => [
                        "Accept" => "application/json",
                        "Authorization" => "Bearer $this->token",
                    ],
                ]);
                $usersResults = json_decode($usersResponse->getBody());

                $this->users = $usersResults->data;

                Cache::set("noke:users", json_encode($this->users), 14400);
            }
        }

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }

        return $this->users;
    }

    public function getGroupProfile($id)
    {
        $url = "{$this->baseUrl}/group/profile/";
        Log::info("Request to $url");

        $response = $this->client->post($url, [
            "json" => [
                "id" => $id,
            ],
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer $this->token",
            ],
        ]);

        $result = json_decode($response->getBody());

        return $result->data;
    }

    public function updateGroup($data)
    {
        $url = "{$this->baseUrl}/group/edit/";
        Log::info("Request to $url");

        if (app()->environment() === "production") {
            $response = $this->client->post($url, [
                "json" => $data,
                "headers" => [
                    "Accept" => "application/json",
                    "Authorization" => "Bearer $this->token",
                ],
            ]);

            return json_decode($response->getBody());
        }
    }

    public function updateUser($data)
    {
        $url = "{$this->baseUrl}/user/edit/";
        Log::info("Request to $url");

        if (app()->environment() === "production") {
            $response = $this->client->post($url, [
                "json" => $data,
                "headers" => [
                    "Accept" => "application/json",
                    "Authorization" => "Bearer $this->token",
                ],
            ]);

            return json_decode($response->getBody());
        }
    }

    private function resetToken()
    {
        if (app()->environment() == "production") {
            $url = "{$this->baseUrl}/company/web/login/";
            Log::info("Request to $url");

            $loginResponse = $this->client->post($url, [
                "json" => [
                    "username" => config("services.noke.username"),
                    "password" => config("services.noke.password"),
                ],
            ]);

            $loginResult = json_decode($loginResponse->getBody());

            if ($loginResult->result === "failure") {
                throw new \Exception("login error");
            }

            $this->token = $loginResult->token;

            Cache::set("noke:token", $this->token, 3600);
        }
    }
}
