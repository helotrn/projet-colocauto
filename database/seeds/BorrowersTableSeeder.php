<?php

use App\Models\Borrower;
use Illuminate\Database\Seeder;

class BorrowersTableSeeder extends Seeder
{
    public function run()
    {
        $borrowers = [
            [
                "id" => 2,
                "user_id" => 2,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 3,
                "user_id" => 3,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 4,
                "user_id" => 4,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 5,
                "user_id" => 5,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 6,
                "user_id" => 6,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 7,
                "user_id" => 7,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 8,
                "user_id" => 8,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 9,
                "user_id" => 9,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 10,
                "user_id" => 10,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 11,
                "user_id" => 11,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 12,
                "user_id" => 12,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 13,
                "user_id" => 13,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 14,
                "user_id" => 14,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 15,
                "user_id" => 15,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 16,
                "user_id" => 16,
                "drivers_license_number" => "",
                "has_not_been_sued_last_ten_years" => true,
                "noke_id" => null,
                "submitted_at" => new DateTime(),
                "approved_at" => new DateTime(),
            ],
            [
                "id" => 17,
                "user_id" => 17,
                "drivers_license_number" => "",
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
