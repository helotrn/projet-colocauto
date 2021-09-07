<?php

use Illuminate\Database\Seeder;

class PassportSeeder extends Seeder
{
    public function run()
    {
        // Only for development purposes, do not use in production
        // Prefer output of a command like `php artisan passport:install`

        \DB::statement(
            "INSERT INTO oauth_clients
(id, name, secret, redirect, personal_access_client, password_client, revoked,
    created_at, updated_at)
VALUES (
    '" .
                env("PASSWORD_CLIENT_ID") .
                "',
    'LocoMotion Password Grant Client',
    '" .
                env("PASSWORD_CLIENT_SECRET") .
                "',
    'http://localhost',
    false,
    true,
    false,
    current_timestamp,
    current_timestamp
) ON CONFLICT DO NOTHING"
        );
    }
}
