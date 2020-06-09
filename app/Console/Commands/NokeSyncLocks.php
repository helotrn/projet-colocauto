<?php

namespace App\Console\Commands;

use App\Models\Padlock;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncLocks extends Command
{
    use NokeCommandTrait;

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
        $this->getGroups(true);

        $this->info('Creating remote groups...');
        $this->createGroups();

        $this->info('Done.');
    }

    private function syncLocks() {
        $locksResult = $this->getLocks(true);

        $lockIds = [];
        foreach ($locksResult as $nokeLock) {
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

        $this->info('Removing defunct locks...');
        $removedLocks = Padlock::whereNotIn('id', $lockIds);
        if ($removedLocks->count() === 0) {
              $this->warn('No lock to remove.');
        }
        foreach ($removedLocks as $lock) {
            $this->warn("Removing lock $nokeLock->name ({$nokeLock->id}).");
            $lock->destroy();
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
                                    'startDate' => '2020-05-01T00:00:00-04:00',
                                    'endDate' => '2030-05-01T23:59:59-04:00',
                                    'expiration' => '2030-05-01T23:59:59-04:00',
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
