<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Padlock;
use App\Services\NokeService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;

class NokeSyncLoans extends Command
{
    protected $signature = 'noke:sync:loans
                            {--pretend : Do not call remote API}';

    protected $description = 'Synchronize NOKE loans';

    protected $groups = [];
    protected $groupsIndex = [];

    protected $locksIndex = [];

    protected $users = [];
    protected $usersIndex = [];

    private $pretend = false;

    public function __construct(Client $client, NokeService $service) {
        parent::__construct();

        $this->client = $client;
        $this->service = $service;
    }

    public function handle() {
        if ($this->option('pretend') || app()->environment() !== 'production') {
            $this->pretend = true;
        }

        $this->info('Fetching locks...');
        $this->getLocks();

        $this->info('Fetching users...');
        $this->getUsers();

        $this->info('Fetching groups...');
        $this->getGroups();

        $this->info('Building locks users...');
        $this->buildLocksUsers();

        $this->info('Updating locks users...');
        $this->updateLocksUsers();

        $this->info('Done.');
    }

    protected function getLocks($force = false) {
        $this->locks = $this->service->fetchLocks($force);

        foreach ($this->locks as $lock) {
            $this->locksIndex[$lock->macAddress] = $lock;
            $this->locksIndex[$lock->macAddress]->users = [];
        }
    }

    protected function getGroups($force = false) {
        $this->groups = $this->service->fetchGroups($force);

        foreach ($this->groups as $group) {
            $this->groupsIndex[$group->name] = $group;
        }
    }

    protected function getUsers($force = false) {
        $this->users = $this->service->fetchUsers($force);

        foreach ($this->users as $user) {
            $this->usersIndex[$user->username] = $user;
        }
    }

    private function buildLocksUsers() {
        $macAddresses = array_keys($this->locksIndex);

        foreach ($macAddresses as $mac) {
            if (!isset($this->locksIndex[$mac]->users)) {
                $this->locksIndex[$mac]->users = [];
            }

            $columnDefinitions = Loan::getColumnsDefinition();
            $query = Loan::where('departure_at', '<=', new \DateTime('- 15 minutes'))
                ->whereHas('prePayment', function ($q) {
                    return $q->where('status', 'completed');
                })
                ->where(function ($q) {
                    return $q->whereHas('payment', function ($q) {
                        return $q->where('status', '!=', 'completed');
                    })->orWhereDoesntHave('payment');
                })
                ->whereHas('loanable', function ($q) use ($mac) {
                    return $q->whereHas('padlock', function ($q) use ($mac) {
                        return $q->where('mac_address', $mac);
                    });
                });
            $query = $columnDefinitions['loan_status']($query);
            $query = $columnDefinitions['*']($query);

            $query->with('borrower', 'borrower.user');

            $loans = $query->get();

            if ($loans->count() > 0) {
                foreach ($loans as $loan) {
                    $this->locksIndex[$mac]->users[] = $loan->borrower->user->email;
                }
            }
        }
    }

    private function updateLocksUsers() {
        $macAddresses = array_keys($this->locksIndex);

        foreach ($macAddresses as $mac) {
            $groupName = "API $mac";

            if (!isset($this->groupsIndex[$groupName])) {
                break;
            }

            $data = $this->groupsIndex[$groupName];

            $data->userIds = [];

            foreach ($this->locksIndex[$mac]->users as $email) {
                if (isset($this->usersIndex[$email])) {
                    $data->userIds[] = $this->usersIndex[$email]->id;
                } else {
                    $this->error("User not found: $email!");
                }
            }

            $data->userIds[] = $this->usersIndex['api@locomotion.app']->id;
            $data->lockIds = [$this->locksIndex[$mac]->id];

            $group = $this->service->getGroupProfile($data->id);
            $currentUserIds = array_map(function ($u) {
                return $u->id;
            }, $group->users);
            Log::channel('noke')->info("Group $groupName has " . join(',', $currentUserIds));

            $data->userIds = array_values(array_unique($data->userIds));
            if (empty(array_diff($data->userIds, $currentUserIds))
                && count($data->userIds) === count($currentUserIds)) {
                continue;
            }

            $this->warn("Updating group {$groupName} users.");
            $userIds = join(',', $data->userIds);
            Log::channel('noke')->info("Updating $groupName with $userIds");

            if ($this->pretend) {
                continue;
            }
            var_dump('here');

            //$this->service->updateGroup($data);
        }
    }
}
