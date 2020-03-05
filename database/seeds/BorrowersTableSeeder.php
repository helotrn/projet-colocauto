<?php

use App\Models\Borrower;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BorrowersTableSeeder extends Seeder
{
    public function run() {
        $borrowers = [
            [
                'id' => 1,
                'user_id' => 2,
                'submitted_at' => new DateTime,
                'approved_at' => null,
                'drivers_license_number' => null,
                'has_been_sued_last_ten_years' => false,
                'noke_id' => null
            ],
        ];

        foreach ($borrowers as $borrower) {
            if (!Borrower::where('id', $borrower{'id'})->exists()) {
                Borrower::create($borrower);
            } else {
                Borrower::where('id', $borrower['id'])->update($borrower);
            }
        }

        \DB::statement("SELECT setval('bikes_id_seq'::regclass, 2)");
    }
}
