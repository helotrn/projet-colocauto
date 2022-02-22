<?php

use App\Models\Padlock;
use Illuminate\Database\Seeder;

class PadlocksTableSeeder extends Seeder
{
    public function run()
    {
        $nUnaffectedPadlocks = 100;

        // Bikes
        factory(Padlock::class)->create([
            "loanable_id" => 1,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 2,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 3,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 102,
        ]);

        // Trailers
        factory(Padlock::class)->create([
            "loanable_id" => 2001,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 2002,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 2003,
        ]);
        factory(Padlock::class)->create([
            "loanable_id" => 2102,
        ]);

        for ($p = 0; $p < $nUnaffectedPadlocks; ++$p) {
            factory(Padlock::class)->create();
        }

        \DB::statement(
            "SELECT setval('padlocks_id_seq'::regclass, (SELECT MAX(id) FROM padlocks) + 1)"
        );
    }
}
