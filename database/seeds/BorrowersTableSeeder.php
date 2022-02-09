<?php

use App\Models\Borrower;
use Illuminate\Database\Seeder;

class BorrowersTableSeeder extends Seeder
{
    public function run()
    {
        $borrowers = [
            [
                // emprunteur@locomotion.app
                "id" => 3,
                "user_id" => 3,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                // emprunteurahuntsic@locomotion.app
                "id" => 4,
                "user_id" => 4,
                "drivers_license_number" => "L1234-456789-01",
                "has_been_sued_last_ten_years" => false,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                // emprunteurpetitepatrie@locomotion.app
                "id" => 6,
                "user_id" => 6,
                "drivers_license_number" => "L1234-456789-01",
                "has_been_sued_last_ten_years" => false,
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
