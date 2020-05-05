<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run() {
        $this->call(CommunitiesTableSeeder::class);
        if (app()->environment() === 'local') {
            $this->call(ImagesTableSeeder::class);
            $this->call(UsersTableSeeder::class);
            $this->call(OwnersTableSeeder::class);
            $this->call(BikesTableSeeder::class);
            $this->call(BorrowersTableSeeder::class);
            $this->call(LoansTableSeeder::class);
            $this->call(PricingsTableSeeder::class);
            $this->call(PassportSeeder::class);
        }
        $this->call(TagsTableSeeder::class);
    }
}
