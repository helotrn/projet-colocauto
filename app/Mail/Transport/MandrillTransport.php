<?php

namespace App\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Swift_Mime_SimpleMessage;

class MandrillTransport extends Transport
{
    protected $client;
    protected $key;

    public function __construct(ClientInterface $client, string $key)
    {
        $this->key = $key;
        $this->client = $client;
    }

    public function send(
        Swift_Mime_SimpleMessage $message,
        &$failedRecipients = null
    ) {
        $this->beforeSendPerformed($message);

        if (property_exists($message, "template") && !!$message->template) {
            $response = $this->sendTemplate($message);
        } elseif (property_exists($message, "raw") && !!$message->raw) {
            $response = $this->sendRaw($message);
        } else {
            $response = $this->sendJson($message);
        }

        $message
            ->getHeaders()
            ->addTextHeader("X-Message-ID", $this->getMessageId($response));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    protected function getMessageId(ResponseInterface $response)
    {
        $response = json_decode((string) $response->getBody(), true);

        if (
            !in_array(Arr::get($response, "0.status"), [
                "sent",
                "rejected",
                "queued",
            ])
        ) {
            throw new \Exception("Failed sending message"); // FIXME better error handling
        }

        return Arr::get($response, "0._id");
    }

    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        $to = [];

        if ($message->getTo()) {
            $to = array_merge($to, $message->getTo());
        }

        if ($message->getCc()) {
            $to = array_merge($to, $message->getCc());
        }

        if ($message->getBcc()) {
            $to = array_merge($to, $message->getBcc());
        }

        return $to;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        return $this->key = $key;
    }

    private function sendTemplate($message)
    {
        $templateContent = [
            [
                "name" => "main",
                "content" => $message->getBody(),
            ],
        ];

        foreach ($message->templateVars as $name => $content) {
            $templateContent[$name] = (string) $content;
        }

        $params = [
            "key" => $this->key,
            "template_name" => $message->template,
            "template_content" => $templateContent,
            "message" => [
                "subject" => $message->getSubject(),
                "from_email" => array_keys($message->getFrom())[0],
                "from_name" => array_values($message->getFrom())[0],
                "to" => array_map(
                    function ($name, $email) {
                        return [
                            "email" => $email,
                            "name" => $name,
                            "type" => "to",
                        ];
                    },
                    $this->getTo($message),
                    array_keys($this->getTo($message))
                ),
                "tags" => [
                    "locomotion",
                    "locomotion_template",
                    "locomotion_template_$message->template",
                ],
            ],
        ];

        return $this->client->request(
            "POST",
            "https://mandrillapp.com/api/1.0/messages/send-template.json",
            ["json" => $params]
        );
    }

    private function sendJson($message)
    {
        try {
            return $this->client->request(
                "POST",
                "https://mandrillapp.com/api/1.0/messages/send.json",
                [
                    "json" => [
                        "key" => $this->key,
                        "async" => false,
                        "ip_pool" => "",
                        "send_at" => null,
                        "message" => [
                            "html" => $message->getBody(),
                            "text" => $message->getChildren()[0]->getBody(),
                            "subject" => $message->getSubject(),
                            "from_email" => array_keys($message->getFrom())[0],
                            "from_name" => array_values($message->getFrom())[0],
                            "to" => array_map(
                                function ($name, $email) {
                                    return [
                                        "email" => $email,
                                        "name" => $name,
                                        "type" => "bcc",
                                    ];
                                },
                                $this->getTo($message),
                                array_keys($this->getTo($message))
                            ),
                            "headers" => [
                                "Reply-To" => array_keys(
                                    $message->getFrom()
                                )[0],
                            ],
                            "important" => false,
                            "track_opens" => $message->trackable,
                            "track_clicks" => $message->trackable,
                            "auto_text" => null,
                            "auto_html" => null,
                            "inline_css" => true,
                            "url_strip_qs" => null,
                            "preserve_recipients" => false,
                            "view_content_link" => null,
                            "tracking_domain" => null,
                            "signing_domain" => null,
                            "return_path_domain" => null,
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    private function sendRaw($message)
    {
        return $this->client->request(
            "POST",
            "https://mandrillapp.com/api/1.0/messages/send-raw.json",
            [
                "form_params" => [
                    "key" => $this->key,
                    "to" => array_keys($this->getTo($message)),
                    "raw_message" => $message->toString(),
                    "async" => true,
                ],
            ]
        );
    }
}
