<?php

use App\Models\Padlock;
use Illuminate\Database\Seeder;

class PadlocksTableSeeder extends Seeder
{
    public function run()
    {
        $nUnaffectedPadlocks = 100;

        for ($p = 0; $p < $nUnaffectedPadlocks; ++$p) {
            factory(Padlock::class)->create();
        }

        \DB::statement(
            "SELECT setval('padlocks_id_seq'::regclass, (SELECT MAX(id) FROM padlocks) + 1)"
        );
    }
}
