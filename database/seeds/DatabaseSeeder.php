<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        switch (app()->environment()) {
            case "local":
            case "staging":
                $this->call(CommunitiesTableSeeder::class);
                $this->call(TagsTableSeeder::class);

                $this->call(ImagesTableSeeder::class);
                $this->call(FilesTableSeeder::class);
                $this->call(UsersTableSeeder::class);
                $this->call(OwnersTableSeeder::class);
                $this->call(PadlocksTableSeeder::class);
                $this->call(BikesTableSeeder::class);
                $this->call(CarsTableSeeder::class);
                $this->call(TrailersTableSeeder::class);
                $this->call(BorrowersTableSeeder::class);
                $this->call(LoansTableSeeder::class);

                $this->call(ProductionPricingsTableSeeder::class);
                break;
            case "production":
                //$this->call(CommunitiesTableSeeder::class);
                //$this->call(TagsTableSeeder::class);
                //$this->call(ProductionPricingsTableSeeder::class);
                //$this->call(AdminsTableSeeder::class);
                break;
        }

        if (app()->environment() === "local") {
            $this->call(PassportSeeder::class);
        }
    }
}
