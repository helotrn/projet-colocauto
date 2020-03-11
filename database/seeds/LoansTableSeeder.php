<?php

use App\Models\Loan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LoansTableSeeder extends Seeder
{
    public function run() {
        $loans = [
            [
                'id' => 1,
                'departure_at' => now(),
                'duration_in_minutes' => 120,
                'borrower_id' => 1,
                'loanable_type' => 'bike',
                'loanable_id' => 1,
                'estimated_distance' => 20,
            ],
        ];

        foreach ($loans as $loan) {
            if (!Loan::where('id', $loan['id'])->exists()) {
                Loan::create($loan);
            } else {
                Loan::where('id', $loan['id'])->first()->update($loan);
            }
        }

        \DB::statement("SELECT setval('loans_id_seq'::regclass, (SELECT MAX(id) FROM loans) + 1)");
    }
}
