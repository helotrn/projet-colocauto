<?php

namespace App\Console\Commands;

use App\Models\Padlock;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncLocks extends Command
{
    private $baseUrl = 'https://v1-api-nokepro.appspot.com';
    private $token;
    private $groups = [];
    private $groupsIndex = [];

    protected $signature = 'noke:sync:locks';

    protected $description = 'Synchronize NOKE locks configuration';

    public function __construct(Client $client) {
        parent::__construct();

        $this->client = $client;
    }

    public function handle() {
        $this->info('Logging in...');
        $this->login();

        $this->info('Synchronizing local locks...');
        $this->syncLocks();

        $this->info('Fetching groups...');
        $this->getGroups();

        $this->info('Creating remote groups...');
        $this->createGroups();

        $this->info('Done.');
    }

    private function login() {
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

    private function syncLocks() {
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

        $lockIds = [];
        foreach ($locksResult->data as $nokeLock) {
            $lock = Padlock::whereExternalId($nokeLock->id)->first();
            if (!$lock) {
                $lock = new Padlock;
                $this->warn("Creating lock $nokeLock->name ({$nokeLock->id}).");
            } else {
                $this->warn("Updating lock $nokeLock->name ({$nokeLock->id}).");
            }

            $lock->external_id = $nokeLock->id;
            $lock->name = $nokeLock->name;
            $lock->mac_address = $nokeLock->macAddress;
            $lock->deleted_at = null;
            $lock->save();

            $lockIds[] = $lock->id;
        }

        $this->warn('Removing defunct locks...');
        $removedLocks = Padlock::whereNotIn('id', $lockIds);
        if ($removedLocks->count() === 0) {
              $this->warn('No lock to remove.');
        }
        foreach ($removedLocks as $lock) {
            $this->warn("Removing lock $nokeLock->name ({$nokeLock->id}).");
            $lock->destroy();
        }
    }

    private function getGroups() {
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

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }
    }

    private function createGroups() {
        $locks = Padlock::all();
        foreach ($locks as $lock) {
            $groupName = "API {$lock->mac_address}";
            if (!isset($this->groupsIndex[$groupName])) {
                $this->warn("Creating group {$groupName}.");
                $this->client->post(
                    "{$this->baseUrl}/group/create/",
                    [
                        'json' => [
                            'name' => $groupName,
                            'groupType' => 'online',
                            'lockIds' => [ intval($lock->external_id) ],
                            'userIds' => [ intval(config('services.noke.api_user_id')) ],
                            'schedule' => [
                                [
                                    'startDate' => '2020-04-01T12:00:00-04:00',
                                    'endDate' => '2020-04-01T13:00:00-04:00',
                                    'expiration' => '2020-04-01T13:00:00-04:00',
                                    'repeatType' => 'none',
                                    'dayOfWeek' => '',
                                    'name' => strval(time()),
                                ]
                            ],
                        ],
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
