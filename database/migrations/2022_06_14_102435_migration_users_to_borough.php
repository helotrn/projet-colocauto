<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Community;
use App\Models\Pivots\CommunityUser;

class MigrationUsersToBorough extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Don't load users here. We'll load them from community to community to save on memory.
        $neighborhoods = Community::where("type", "=", "neighborhood")->get();

        foreach ($neighborhoods as $neighborhood) {
            Log::info(
                "Community: (" .
                    $neighborhood->type .
                    ") " .
                    $neighborhood->name
            );

            // Neighborhoods without parent borough were found in
            // staging. Just make sure it does not crash the migration
            // process.
            if ($neighborhood->parent) {
                $communityUsers = CommunityUser::where(
                    "community_id",
                    "=",
                    $neighborhood->id
                )->get();

                foreach ($communityUsers as $communityUser) {
                    CommunityUser::withoutEvents(function () use (
                        $communityUser,
                        $neighborhood
                    ) {
                        Log::info(
                            sprintf(
                                "    Updating user with id %4d: %s (%d) -> %s (%d)",
                                $communityUser->user_id,
                                $neighborhood->name,
                                $neighborhood->id,
                                $neighborhood->parent->name,
                                $neighborhood->parent_id
                            )
                        );

                        $communityUser->community_id = $neighborhood->parent_id;
                        $communityUser->save();
                    });
                }

                unset($communityUsers);
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
