<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run() {
        $this->call(CommunitiesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(OwnersTableSeeder::class);
        $this->call(BikesTableSeeder::class);
        $this->call(PassportSeeder::class);
    }
}
