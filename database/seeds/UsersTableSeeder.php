<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run() {
        $generic = [
            'password' => 'molotov',
            'date_of_birth' => '2009-01-01',
            'address' => '2065 rue Parthenais',
            'postal_code' => 'H2K 3T1',
            'phone' => '514-908-9744',
            'description' => 'Communications alternatives',
        ];

        $users = [
            'soutien@molotov.ca' => array_merge([
                'role' => 'admin',
                'name' => 'Molotov Communications',
            ], $generic),
            'emile@molotov.ca' => array_merge($generic, [
                'name' => 'Émile',
                'last_name' => 'Plourde-Lavoie',
                'description' => 'Salut tout le monde :)',
                'submitted_at' => new \DateTime,
            ]),
            'ariane@molotov.ca' => array_merge($generic, [
                'name' => 'Ariane',
                'last_name' => 'Mercier',
                'submitted_at' => new \DateTime,
            ]),
            'alexandre@molotov.ca' => array_merge($generic, [
                'name' => 'Alexandre',
                'last_name' => 'Chouinard',
                'role' => 'admin',
                'description' => 'Autre admin global',
            ]),
            'achouinard31@gmail.com' => [
                'name' => 'Alexandre',
                'last_name' => 'Chouinard',
                'description' => 'Description',
            ],
        ];

        $memberships = [
            'soutien@molotov.ca' => [],
            'emile@molotov.ca' => [
                1 => [
                    'role' => 'admin',
                    'approved_at' => new \DateTime,
                ],
            ],
            'ariane@molotov.ca' => [
                1 => [
                    'approved_at' => new \DateTime,
                ],
            ],
            'achouinard31@gmail.com' => [
                1 => [
                    'role' => 'admin',
                ],
            ],
        ];

        $id = 1;
        foreach ($users as $email => $data) {
            $data = array_merge($data, [
                'id' => $id,
                'email' => $email,
                'password' => Hash::make(array_get($data, 'password', 'password')),
            ]);

            if (!User::where('email', $email)->exists()) {
                User::create($data);
            } else {
                User::where('email', $email)->update($data);
            }

            $id += 1;
        }

        foreach ($memberships as $email => $communities) {
            $user = User::where('email', $email)->first();

            $user->communities()->sync($communities);
        }

        \DB::statement("SELECT setval('users_id_seq'::regclass, (SELECT MAX(id) FROM users) + 1)");
    }
}
