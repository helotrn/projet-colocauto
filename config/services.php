<?php

return [
    "google" => [
        "client_id" => env("GOOGLE_CLIENT_ID"),
        "client_secret" => env("GOOGLE_CLIENT_SECRET"),
        "redirect" => env("GOOGLE_REDIRECT"),
    ],

    "mailchimp" => [
        "server_prefix" => env("MAILCHIMP_SERVER_PREFIX"),
        "key" => env("MAILCHIMP_KEY"),
        "newsletter_list_id" => env("MAILCHIMP_LIST_ID"),
    ],

    "mattermost" => [
        "main_hook_url" => env("MATTERMOST_MAIN_HOOK_URL"),
    ],

    "mailgun" => [
        "domain" => env("MAILGUN_DOMAIN"),
        "secret" => env("MAILGUN_SECRET"),
        "endpoint" => env("MAILGUN_ENDPOINT", "api.mailgun.net"),
    ],

    "mandrill" => [
        "secret" => env("MANDRILL_KEY"),
    ],

    "noke" => [
        "api_user_id" => env("NOKE_API_USER_ID"),
        "username" => env("NOKE_USERNAME"),
        "password" => env("NOKE_PASSWORD"),
    ],

    "postmark" => [
        "token" => env("POSTMARK_TOKEN"),
    ],

    "ses" => [
        "key" => env("AWS_ACCESS_KEY_ID"),
        "secret" => env("AWS_SECRET_ACCESS_KEY"),
        "region" => env("AWS_DEFAULT_REGION", "us-east-1"),
    ],

    "sparkpost" => [
        "secret" => env("SPARKPOST_SECRET"),
    ],

    "stripe" => [
        "key" => env("STRIPE_KEY", "sk_test"),
        "secret" => env("STRIPE_SECRET", "pk_test"),
    ],
];
