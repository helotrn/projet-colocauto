<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run() {
        $users = [
            'soutien@molotov.ca' => [
                'id' => 1,
                'password' => 'molotov',
                'role' => 'admin',
                'name' => 'Molotov Communications',
                'description' => 'Communications alternatives',
                'date_of_birth' => '2009-01-01',
                'address' => '2065 rue Parthenais',
                'postal_code' => 'H2K 3T1',
                'phone' => '514-908-9744',
            ],
            'emile@molotov.ca' => [
                'id' => 2,
                'password' => 'molotov',
                'name' => 'Émile',
                'last_name' => 'Plourde-Lavoie',
                'description' => 'Salut tout le monde :)',
                'date_of_birth' => '2009-01-01',
                'address' => '2065 rue Parthenais',
                'postal_code' => 'H2K 3T1',
                'phone' => '514-908-9744',
            ],
            'ariane@molotov.ca' => [
                'id' => 3,
                'password' => 'molotov',
                'name' => 'Ariane',
                'last_name' => 'Mercier',
            ],
            'alexandre@molotov.ca' => [
                'id' => 4,
                'password' => 'molotov',
                'name' => 'Alexandre',
                'last_name' => 'Chouinard',
                'role' => 'admin',
            ],
            'achouinard31@gmail.com' => [
                'id' => 5,
                'password' => 'molotov',
                'name' => 'Alexandre',
                'last_name' => 'Chouinard',
            ],
        ];

        $memberships = [
            'soutien@molotov.ca' => [],
            'emile@molotov.ca' => [
                1 => [
                    'role' => 'admin',
                ],
            ],
            'achouinard31@gmail.com' => [
                1 => [
                    'role' => 'admin',
                ],
            ],
        ];

        foreach ($users as $email => $data) {
            $data = array_merge($data, [
                'email' => $email,
                'password' => Hash::make(array_get($data, 'password', 'password')),
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

        \DB::statement("SELECT setval('users_id_seq'::regclass, (SELECT MAX(id) FROM users) + 1)");
    }
}
