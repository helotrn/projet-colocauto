<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run() {
        $users = [
            'soutien@molotov.ca' => [
                'password' => 'molotov',
                'role' => 'admin',
                'name' => 'Molotov Communications',
            ],
            'emile@molotov.ca' => [
                'password' => 'molotov',
                'name' => 'Émile',
                'last_name' => 'Plourde-Lavoie',
            ],
        ];

        $memberships = [
            'soutien@molotov.ca' => [],
            'emile@molotov.ca' => [
                1 => [
                    'role' => 'admin',
                ],
            ],
        ];

        foreach ($users as $email => $data) {
            $data = array_merge($data, [
                'email' => $email,
                'password' => Hash::make(dig($data, 'password', 'password')),
            ]);

            if (!User::where('email', $email)->exists()) {
                User::create($data);
            } else {
                User::where('email', $email)->update($data);
            }
        }

        foreach ($memberships as $email => $communities) {
            $user = User::where('email', $email)->first();

            $user->communities()->sync($communities);
        }
    }
}
