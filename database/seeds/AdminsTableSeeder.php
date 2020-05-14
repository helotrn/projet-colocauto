<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
    public function run() {
        $admins = [
            'soutien@molotov.ca' => [
                'role' => 'admin',
                'name' => 'Molotov Communications',
            ],
        ];

        foreach ($users as $email => $data) {
            $data = array_merge($data, [
                'email' => $email,
                'password' => Hash::make(array_get($data, 'password', md5(rand(1, 100000)))),
            ]);

            if (!User::where('email', $email)->exists()) {
                User::create($data);
            } else {
                User::where('email', $email)->update($data);
            }
        }

        \DB::statement("SELECT setval('users_id_seq'::regclass, (SELECT MAX(id) FROM users) + 1)");
    }
}
