<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use ThibaudDauce\Mattermost\Mattermost;
use ThibaudDauce\Mattermost\Message;
use ThibaudDauce\Mattermost\Attachment;

/** MATTERMOST NOTIFICATIONS
 * *
 * This service has been created on top of thibaud-dauce/mattermost-php
 * for the sake of simplifying the use of Mattermost notification inside the app.
 * It relies on Mattermost Webhook and isn't using their API directly.
 *
 * Documentation: https://github.com/ThibaudDauce/mattermost-php
 */

class MattermostNotificationsService
{
    public function __construct()
    {
        // Nothing yet
    }
    /**
     * CREATE A NOTIFICATION ON MATTERMOST
     * $message can be a string or a Message object (see documentation)
     *
     * Examples:
     *
     * MattermostNotifications::send("A new notification has been created on the default channel");
     * OR
     * MattermostNotifications::send((new Message())->text("New notification")->channel("test"));
     *
     */
    public static function send($message)
    {
        if (app()->environment() === "local") {
            Log::info("Would send mattermost notification: '$message'");
            return;
        }

        $mattermost = new Mattermost(new Client());
        if (gettype($message) == "string") {
            $message = (new Message())
                ->text($message)
                ->channel(env("MATTERMOST_DEFAULT_CHANNEL"));
        }
        $mattermost->send($message, env("MATTERMOST_MAIN_HOOK_URL"));
    }
}
