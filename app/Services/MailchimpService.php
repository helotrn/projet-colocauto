<?php

namespace App\Services;

use MailchimpMarketing\ApiClient as MailchimpApiClient;

use Log;

class MailchimpService
{
    private $config = [];

    private $apiClient = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getApiCLient()
    {
        if (null == $this->apiClient) {
            $this->apiClient = (new MailchimpApiClient())->setConfig([
                "apiKey" => $this->config["key"],
                "server" => $this->config["server_prefix"],
            ]);
        }

        return $this->apiClient;
    }

    public function getMailchimpUser($appUser)
    {
        $mailchimpUser = [
            "email_address" => $appUser->email,
            "status" => "subscribed",
            "merge_fields" => [
                "FNAME" => $appUser->name,
                "LNAME" => $appUser->last_name,
                "CODEPOSTAL" => $appUser->postal_code,
                "MMERGE6" => $appUser->phone,
                "ADDRESS" => $appUser->address,
            ],
        ];

        return $mailchimpUser;
    }

    public function addToListOrUpdate($appUser)
    {
        $apiClient = $this->getApiClient();

        try {
            $mailchimpUser = $this->getMailchimpUser($appUser);

            $mailchimpMember = $this->getMemberFromEmail($appUser->email);

            if (!$mailchimpMember) {
                Log::debug("New");
                // The user is not in the list. We add him
                $apiClient->lists->addListMember(
                    $this->config["newsletter_list_id"],
                    $mailchimpUser
                );
            } else {
                Log::debug("Update");
                // The user is in the list, we update it's info
                $apiClient->lists->setListMember(
                    $this->config["newsletter_list_id"],
                    $mailchimpMember->id,
                    $mailchimpUser
                );
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }

    public function getMemberFromEmail($email)
    {
        $apiClient = $this->getApiClient();

        try {
            $searchResponse = $apiClient->searchMembers->search($email);

            if ($searchResponse->exact_matches->total_items > 0) {
                return $searchResponse->exact_matches->members[0];
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }

        return null;
    }
}
