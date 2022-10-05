<?php

namespace App\Console\Commands;

use App\Models\Padlock;
use App\Services\NokeService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;

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

        Log::info("Fetching locks...");
        $this->locks = $this->service->fetchLocks();
        Log::info("Found " . count($this->locks) . " locks.");

        Log::info("Synchronizing local locks...");
        $this->syncLocks();

        Log::info("Fetching groups...");
        $this->getGroups();
        Log::info("Found " . count($this->groups) . " groups.");

        Log::info("Creating remote groups...");
        $this->createGroups();

        Log::info("Done.");
    }

    private function getGroups()
    {
        $this->groups = $this->service->fetchGroups();

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }
    }

    private function syncLocks()
    {
        $lockIds = [];
        foreach ($this->locks as $nokeLock) {
            $lock = Padlock::whereMacAddress($nokeLock->macAddress)->first();
            if (!$lock) {
                $lock = new Padlock();
                Log::info(
                    "Creating lock $nokeLock->name ({$lock->id} / {$nokeLock->id} / {$nokeLock->macAddress})."
                );
            } else {
                Log::info(
                    "Updating lock $lock->name -> $nokeLock->name ({$lock->id} / {$lock->external_id} -> {$nokeLock->id} / {$lock->mac_address})."
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

        Log::info("Removing defunct locks...");
        $removedLocks = Padlock::whereNotIn("id", $lockIds)->get();
        if ($removedLocks->count() === 0) {
            Log::info("No lock to remove.");
        }

        foreach ($removedLocks as $lock) {
            Log::info(
                "Removing lock $lock->name ({$lock->id} / {$lock->external_id} / {$lock->mac_address})."
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
                Log::info("Creating group {$groupName}.");

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
