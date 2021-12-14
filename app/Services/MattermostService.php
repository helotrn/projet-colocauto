<?php

namespace App\Services;

use GuzzleHttp\Client;
use ThibaudDauce\Mattermost\Mattermost;
use ThibaudDauce\Mattermost\Message;
use ThibaudDauce\Mattermost\Attachment;
use Log;

class MattermostService
{
    private $config = [];
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function sendNotification($message)
    {
        $mattermost = new Mattermost(new Client());
        $mattermost->send($message, $this->config["main_hook_url"]);
    }
}
