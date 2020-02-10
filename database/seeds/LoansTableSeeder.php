<?php

use App\Models\Loan;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class LoansTableSeeder extends Seeder
{
    public function run(Faker $faker) {
        $loans = [
            [
                'id' => 1,
                'departure_at' => $faker->dateTime($format = 'Y-m-d H:i:sO'),
                'duration_in_minutes' => $faker->randomNumber($nbDigits = 4, $strict = false),
                'borrower_id' => 1,
                'loanable_type' => 'bike',
                'loanable_id' => 1,
            ],
        ];

        foreach ($loans as $loan) {
            if (!Loan::where('id', $loan['id'])->exists()) {
                Loan::create($loan);
            } else {
                Loan::where('id', $loan['id'])->first()->update($loan);
            }
        }
    }
}
