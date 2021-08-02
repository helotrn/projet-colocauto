<?php

use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnersTableSeeder extends Seeder
{
    public function run()
    {
        $owners = [
            [
                "id" => 1,
                "user_id" => 2,
                "submitted_at" => new DateTime(),
            ],
            [
                // proprietairevoiture@locomotion.app
                "id" => 4,
                "user_id" => 4,
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
