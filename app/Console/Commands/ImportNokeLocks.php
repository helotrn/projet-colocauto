<?php

namespace App\Console\Commands;

use App\Models\Padlock;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class ImportNokeLocks extends Command
{
    private $baseUrl = 'https://v1-api-nokepro.appspot.com';

    protected $signature = 'import:noke:locks';

    protected $description = 'Import noke locks';

    public function __construct(Client $client) {
        parent::__construct();

        $this->client = $client;
    }

    public function handle() {
        //$loginResponse = $this->client->post("{$this->baseUrl}/company/web/login/", [
        //    'json' => [
        //        'username' => config('services.noke.username'),
        //        'password' => config('services.noke.password'),
        //    ],
        //]);

        //$loginResult = json_decode($loginResponse->getBody());

        //if ($loginResult->result === 'failure') {
        //    throw new \Exception('login error');
        //}

        //$token = $loginResult->token;

        $token = 'eyJhbGciOiJOT0tFIiwidHlwIjoiSldUIn0.eyJhbGciOiJOT0tFIiwiY29tcGFueSI6MTAwMDE4MiwiZXhwIjoxNTg2MjgzNzIwLCJpc3MiOiJub2tlLmNvbSIsImxvZ291dElkIjoiIiwibm9rZVVzZXIiOjQwNTc4LCJ0b2tlblR5cGUiOiJzaWduSW4ifQ.0cb00f7ab30a9dc7df7adc9fcdf5dfe12144662a';

        $locksResponse = $this->client->post("{$this->baseUrl}/lock/get/list/", [
            'json' => [
                'page' => 1,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $token",
            ],
        ]);

        $locksResult = json_decode($locksResponse->getBody());

        foreach ($locksResult->data as $nokeLock) {
            $lock = Padlock::whereExternalId($nokeLock->id)->first();
            if (!$lock) {
                $lock = new Padlock;
                $this->warn("Creating lock $nokeLock->name ({$nokeLock->id})");
            } else {
                $this->warn("Updating lock $nokeLock->name ({$nokeLock->id})");
            }

            $lock->external_id = $nokeLock->id;
            $lock->name = $nokeLock->name;
            $lock->mac_address = $nokeLock->macAddress;
            $lock->save();
        }
    }
}
