<?php

use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnersTableSeeder extends Seeder
{
    public function run()
    {
        $owners = [
            [
                // pauline
                "id" => 2,
                "user_id" => 2,
                "submitted_at" => new DateTime(),
            ],
            [
                // camille
                "id" => 7,
                "user_id" => 7,
                "submitted_at" => new DateTime(),
            ],
            [
                // marcelle
                "id" => 10,
                "user_id" => 10,
                "submitted_at" => new DateTime(),
            ],
            [
                // christiane
                "id" => 13,
                "user_id" => 13,
                "submitted_at" => new DateTime(),
            ],
            [
                // alice
                "id" => 14,
                "user_id" => 14,
                "submitted_at" => new DateTime(),
            ],
            [
                // david
                "id" => 17,
                "user_id" => 17,
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
