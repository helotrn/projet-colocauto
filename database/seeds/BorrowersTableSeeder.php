<?php

use App\Models\Borrower;
use Illuminate\Database\Seeder;

class BorrowersTableSeeder extends Seeder
{
    public function run()
    {
        $borrowers = [
            [
                // emprunteurahuntsic@locomotion.app
                "id" => 5,
                "user_id" => 5,
                "drivers_license_number" => "L1234-456789-01",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                // emprunteurpetitepatrie@locomotion.app
                "id" => 7,
                "user_id" => 7,
                "drivers_license_number" => "L1234-456789-01",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
        ];

        foreach ($borrowers as $borrower) {
            if (!Borrower::where("id", $borrower["id"])->exists()) {
                Borrower::withoutEvents(function () use ($borrower) {
                    Borrower::create($borrower);
                });
            } else {
                Borrower::where("id", $borrower["id"])->update($borrower);
            }
        }

        \DB::statement(
            "SELECT setval('borrowers_id_seq'::regclass, (SELECT MAX(id) FROM borrowers) + 1)"
        );
    }
}
