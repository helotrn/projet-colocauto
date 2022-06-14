<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pivots\CommunityUser;
use App\Models\User;

class MigrationUsersToBorough extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Migrates all users from neighborhood to borough
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Not needed
    }
}
