<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run() {
        $users = [
            'soutien@molotov.ca' => 'marxistl337',
        ];

        foreach ($users as $email => $password) {
            $user = \App\Models\User::create([
                'name' => $email,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }
    }
}
