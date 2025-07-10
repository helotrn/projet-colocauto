<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Log;

class BurnOldUnusedInvitations extends Command
{
    protected $signature = 'invitations:deactivate_old';
    protected $description = "Deactivate old unused invitation after one month of inactivity";

    public function handle()
    {
        $invitationExpirationTime = CarbonImmutable::now()->subMonth(1);
        $invitations = self::getOldUnusedInvitation($invitationExpirationTime);

        foreach ($invitations as $invitation) {
            $invitation->status = 'expired';
            $invitation->consume();
            Log::info("Invitation $invitation->id sent to $invitation->email is too old");
        }
    }

    public static function getOldUnusedInvitation($datetime)
    {
        return Invitation::whereNull("consumed_at")
            ->where("updated_at", "<=", $datetime)
            ->get();
    }
}
