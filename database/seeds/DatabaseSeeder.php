<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run() {
        $this->call(CommunitiesTableSeeder::class);
        $this->call(TagsTableSeeder::class);

        switch (app()->environment()) {
            case 'local':
                $this->call(ImagesTableSeeder::class);
                $this->call(UsersTableSeeder::class);
                $this->call(OwnersTableSeeder::class);
                $this->call(BikesTableSeeder::class);
                $this->call(BorrowersTableSeeder::class);
                $this->call(LoansTableSeeder::class);
                $this->call(PricingsTableSeeder::class);
                $this->call(PassportSeeder::class);
                break;
            case 'staging':
            case 'production':
                $this->call(ProductionPricingsTableSeeder::class);
                //$this->call(AdminsTableSeeder::class);
                break;
        }
    }
}
