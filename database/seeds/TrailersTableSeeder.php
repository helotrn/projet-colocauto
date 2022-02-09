<?php

use App\Models\Trailer;
use Illuminate\Database\Seeder;

class TrailersTableSeeder extends Seeder
{
    public function run()
    {
        // Start trailers at 2001
        $trailers = [
            [
                "id" => 2001,
                "name" => "Remorque Solon sans communauté",
                "position" => "45.54471 -73.628796",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "maximum_charge" => "5",
                "availability_mode" => "always",
                // solon@locomotion.app
                "owner_id" => 2,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 2002,
                "name" => "Remorque Solon Ahuntsic",
                "position" => "45.563652 -73.654695",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "maximum_charge" => "5",
                "availability_mode" => "always",
                // solon@locomotion.app
                "owner_id" => 2,
                "community_id" => 8, // 8: Ahuntsic
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 2003,
                "name" => "Remorque Solon Petite-Patrie",
                "position" => "45.540 -73.610",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "maximum_charge" => "5",
                "availability_mode" => "always",
                // solon@locomotion.app
                "owner_id" => 2,
                "community_id" => 9, // 9: Petite-Patrie
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 2101,
                "name" => "Remorque privée sur demande",
                "position" => "45.535 -73.605",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "maximum_charge" => "5",
                "availability_mode" => "never",
                "availability_json" => <<<JSON
[
  {
    "available":true,
    "type":"weekdays",
    "scope":["MO","TU","TH","WE","FR"],
    "period":"00:00-24:00"
  }
]
JSON
                ,
                // proprietairepetitepatrie@locomotion.app
                "owner_id" => 5,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => false,
            ],
            [
                "id" => 2102,
                "name" => "Remorque privée en libre service",
                "position" => "45.540 -73.605",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "maximum_charge" => "5",
                "availability_mode" => "never",
                "availability_json" => <<<JSON
[
  {
    "available":true,
    "type":"weekdays",
    "scope":["MO","TU","TH","WE","FR"],
    "period":"00:00-24:00"
  }
]
JSON
                ,
                // proprietairepetitepatrie@locomotion.app
                "owner_id" => 5,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
        ];

        foreach ($trailers as $trailer) {
            if (!Trailer::where("id", $trailer["id"])->exists()) {
                Trailer::create($trailer);
            } else {
                Trailer::where("id", $trailer["id"])
                    ->first()
                    ->update($trailer);
            }
        }

        \DB::statement(
            "SELECT setval('loanables_id_seq'::regclass, (SELECT MAX(id) FROM loanables) + 1)"
        );
    }
}
