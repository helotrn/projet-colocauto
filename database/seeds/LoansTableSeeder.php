<?php

use App\Models\Loan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LoansTableSeeder extends Seeder
{
    public function run()
    {
        $loans = [
        ];

        foreach ($loans as $loan) {
            if (!Loan::where("id", $loan["id"])->exists()) {
                Loan::create($loan);
            } else {
                Loan::where("id", $loan["id"])
                    ->first()
                    ->update($loan);
            }
        }

        \DB::statement(
            "SELECT setval('loans_id_seq'::regclass, (SELECT MAX(id) FROM loans) + 1)"
        );
    }
}
