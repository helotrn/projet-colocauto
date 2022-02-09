<?php

use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnersTableSeeder extends Seeder
{
    public function run()
    {
        $owners = [
            [
                // solon@locomotion.app
                "id" => 2,
                "user_id" => 2,
                "submitted_at" => new DateTime(),
            ],
            [
                // proprietaireahuntsic@locomotion.app
                "id" => 3,
                "user_id" => 3,
                "submitted_at" => new DateTime(),
            ],
            [
                // proprietairepetitepatrie@locomotion.app
                "id" => 5,
                "user_id" => 5,
                "submitted_at" => new DateTime(),
            ],
        ];

        foreach ($owners as $owner) {
            if (!Owner::where("id", $owner["id"])->exists()) {
                Owner::create($owner);
            } else {
                Owner::where("id", $owner["id"])->update($owner);
            }
        }

        \DB::statement(
            "SELECT setval('owners_id_seq'::regclass, (SELECT MAX(id) FROM owners) + 1)"
        );
    }
}
