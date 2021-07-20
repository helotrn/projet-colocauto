<?php

namespace App\Console\Commands;

use App\Models\Padlock;
use App\Services\NokeService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncLocks extends Command
{
    protected $signature = 'noke:sync:locks --debug
                            {--pretend : Do not call remote API}';

    protected $description = "Synchronize NOKE locks configuration";

    private $groups = [];
    private $groupsIndex = [];

    private $locks = [];

    private $pretend = false;

    public function __construct(Client $client, NokeService $service)
    {
        parent::__construct();

        $this->client = $client;
        $this->service = $service;
    }

    public function handle()
    {
        if ($this->option("pretend")) {
            $this->pretend = true;
        }

        $this->info("Fetching locks...");
        $this->locks = $this->service->fetchLocks(true);
        $this->info("Found " . count($this->locks) . " locks.");

        $this->info("Synchronizing local locks...");
        $this->syncLocks();

        $this->info("Fetching groups...");
        $this->getGroups();
        $this->info("Found " . count($this->groups) . " groups.");

        $this->info("Creating remote groups...");
        $this->createGroups();

        $this->info("Done.");
    }

    private function getGroups()
    {
        $this->groups = $this->service->fetchGroups(true);

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }
    }

    private function syncLocks()
    {
        $lockIds = [];
        foreach ($this->locks as $nokeLock) {
            $lock = Padlock::whereExternalId($nokeLock->id)->first();
            if (!$lock) {
                $lock = new Padlock();
                $this->warn(
                    "Creating lock $nokeLock->name ({$lock->id} / {$nokeLock->id})."
                );
            } else {
                $this->warn(
                    "Updating lock $nokeLock->name ({$lock->id} / {$nokeLock->id})."
                );
            }

            $lock->external_id = $nokeLock->id;
            $lock->name = $nokeLock->name;
            $lock->mac_address = $nokeLock->macAddress;
            $lock->deleted_at = null;

            if (!$this->pretend) {
                $lock->save();
            }

            if ($lock->id) {
                $lockIds[] = $lock->id;
            }
        }

        $this->info("Removing defunct locks...");
        $removedLocks = Padlock::whereNotIn("id", $lockIds)->get();
        if ($removedLocks->count() === 0) {
            $this->warn("No lock to remove.");
        }

        foreach ($removedLocks as $lock) {
            $this->warn(
                "Removing lock $nokeLock->name ({$lock->id} / {$nokeLock->id})."
            );

            if ($this->pretend) {
                continue;
            }

            $lock->delete();
        }
    }

    private function createGroups()
    {
        $locks = Padlock::all();

        foreach ($locks as $lock) {
            $groupName = "API {$lock->mac_address}";

            if (!isset($this->groupsIndex[$groupName])) {
                $this->warn("Creating group {$groupName}.");

                if ($this->pretend) {
                    continue;
                }

                $this->service->findOrCreateGroup(
                    $groupName,
                    $lock->external_id
                );
            }
        }
    }
}
