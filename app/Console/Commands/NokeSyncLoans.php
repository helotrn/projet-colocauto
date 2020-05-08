<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Padlock;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NokeSyncLoans extends Command
{
    use NokeCommandTrait;

    protected $locksIndex = [];

    protected $signature = 'noke:sync:loans';

    protected $description = 'Synchronize NOKE loans';

    public function __construct(Client $client) {
        parent::__construct();

        $this->client = $client;
    }

    public function handle() {
        $this->info('Logging in...');
        $this->login();

        $this->info('Fetching locks...');
        $locks = $this->getLocks();

        foreach ($locks as $lock) {
            $this->locksIndex[$lock->macAddress] = $lock;
            $this->locksIndex[$lock->macAddress]->users = [];
        }

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
            $query = $columnDefinitions['status']($query);
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

            $data = $this->groupsIndex[$groupName];

            $data->userIds = [];

            foreach ($this->locksIndex[$mac]->users as $email) {
                $data->userIds[] = $this->usersIndex[$email]->id;
            }

            $data->userIds[] = $this->usersIndex['api@locomotion.app']->id;
            $data->lockIds = [$this->locksIndex[$mac]->id];

            $group = $this->getGroupProfile($data->id);
            $currentUserIds = array_map(function ($u) {
                return $u->id;
            }, $group->users);

            $data->userIds = array_values(array_unique($data->userIds));
            if (empty(array_diff($data->userIds, $currentUserIds))
                && count($data->userIds) === count($currentUserIds)) {
                continue;
            }

            $this->warn("Updating group {$groupName} users.");

            $response = $this->client->post(
                "{$this->baseUrl}/group/edit/",
                [
                    'json' => $data,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $this->token",
                    ],
                ]
            );
            $result = json_decode($response->getBody());
        }
    }
}
