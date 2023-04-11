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
                $this->call(CarsTableSeeder::class);
                $this->call(BorrowersTableSeeder::class);
                $this->call(LoansTableSeeder::class);

                $this->call(ProductionPricingsTableSeeder::class);

                $this->call(ExpenseTagsSeeder::class);
                $this->call(ExpensesTableSeeder::class);
                $this->call(RefundsTableSeeder::class);
                break;
            case "production":
                $this->call(AdminsTableSeeder::class);
                break;
        }

        if (app()->environment() === "local") {
            $this->call(PassportSeeder::class);
        }
    }
}
