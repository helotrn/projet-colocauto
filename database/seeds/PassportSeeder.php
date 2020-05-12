<?php

use Illuminate\Database\Seeder;

class PassportSeeder extends Seeder
{
    public function run() {
        // Only for development purposes, do not use in production
        // Prefer output of a command like `php artisan passport:install`

        \DB::statement(<<<SQL
INSERT INTO oauth_clients
(id, name, secret, redirect, personal_access_client, password_client, revoked,
    created_at, updated_at)
VALUES (
    'pTrmRvquBD6mXlep',
    'Locomotion Password Grant Client',
    '1hu8xmgtXI8O6HRH4zIbRt92X6rSLCgY6NtTNyxN',
    'http://localhost',
    false,
    true,
    false,
    current_timestamp,
    current_timestamp
) ON CONFLICT DO NOTHING
SQL
        );
    }
}
