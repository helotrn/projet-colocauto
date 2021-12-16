<?php

namespace App\Services;

use GuzzleHttp\Client;
use ThibaudDauce\Mattermost\Mattermost;
use ThibaudDauce\Mattermost\Message;
use ThibaudDauce\Mattermost\Attachment;

/** MATTERMOST NOTIFICATION
 * Documentation: https://github.com/ThibaudDauce/mattermost-php
 */

class MattermostNotificationsService
{
    private $config = [];
    public function __construct($config)
    {
        $this->config = $config;
    }
    /**
     * $message can be a string or a Message object (see documentation)
     */
    public static function send($message)
    {
        $mattermost = new Mattermost(new Client());
        if (gettype($message) == "string") {
            $message = (new Message())
                ->text($message)
                ->channel(env("MATTERMOST_DEFAULT_CHANNEL"));
        }
        $mattermost->send($message, env("MATTERMOST_MAIN_HOOK_URL"));
    }
}
