<?php

namespace App\Console\Commands;

use App\Models\Pivots\CommunityUser;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class UsersMigrationNeighborhood extends Command
{
    protected $signature = "users:migrate:neighborhood";
    protected $description = "Migrates all users from neighborhood to borough";

    private $controller;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $users = User::with("communities")->get();
        foreach ($users as $user) {
            foreach ($user->communities as $community) {
                if ($community->type == "neighborhood" && $community->parent) {
                    // Save Quietly in order to prevent the CommunityUser::saved event to be triggered
                    $communityUser = CommunityUser::withoutEvents(
                        function () use ($community, $user) {
                            // Find the pivot line between Community and User
                            $communityUser = CommunityUser::where(
                                "user_id",
                                $user->id
                            )
                                ->where("community_id", $community->id)
                                ->first();
                            if ($communityUser) {
                                Log::info(
                                    "Updating user " .
                                        $user->id .
                                        "'s neighborhood"
                                );
                                $communityUser->community_id =
                                    $community->parent_id;
                                $communityUser->save();
                            }
                        }
                    );
                }
            }
        }
    }
}
