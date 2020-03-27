<?php

use App\Models\Borrower;
use Illuminate\Database\Seeder;

class BorrowersTableSeeder extends Seeder
{
    public function run() {
        $borrowers = [
            [
                'id' => 1,
                'user_id' => 3,
                'submitted_at' => new DateTime,
                'approved_at' => new DateTime,
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'submitted_at' => new DateTime,
                'approved_at' => new DateTime,
            ],
        ];

        foreach ($borrowers as $borrower) {
            if (!Borrower::where('id', $borrower{'id'})->exists()) {
                Borrower::create($borrower);
            } else {
                Borrower::where('id', $borrower['id'])->update($borrower);
            }
        }

        \DB::statement("SELECT setval('borrowers_id_seq'::regclass, (SELECT MAX(id) FROM borrowers) + 1)");
    }
}
