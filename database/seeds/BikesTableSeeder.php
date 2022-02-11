<?php

use App\Models\Bike;
use Illuminate\Database\Seeder;

class BikesTableSeeder extends Seeder
{
    public function run()
    {
        // Start bikes at 1
        $bikes = [
            [
                "id" => 1,
                "name" => "Vélo Solon sans communauté",
                "position" => "45.54371 -73.627796",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "model" => "Vélo",
                "bike_type" => "regular",
                "size" => "big",
                "availability_mode" => "always",
                // solonpetitepatrie@locomotion.app
                "owner_id" => 3,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 2,
                "name" => "Vélo Solon Ahuntsic",
                "position" => "45.562652 -73.653695",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "model" => "Vélo",
                "bike_type" => "regular",
                "size" => "big",
                "availability_mode" => "always",
                // solonahuntsic@locomotion.app
                "owner_id" => 2,
                "community_id" => 8, // 8: Ahuntsic
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 3,
                "name" => "Vélo Solon Petite-Patrie",
                "position" => "45.540 -73.600",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "model" => "Vélo",
                "bike_type" => "regular",
                "size" => "big",
                "availability_mode" => "always",
                // solonpetitepatrie@locomotion.app
                "owner_id" => 3,
                "community_id" => 9, // 9: Petite-Patrie
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
            [
                "id" => 101,
                "name" => "Vélo de Propriétaire Petite-Patrie sur demande",
                "position" => "45.535 -73.595",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "model" => "Vélo",
                "bike_type" => "regular",
                "size" => "big",
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
                "owner_id" => 6,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => false,
            ],
            [
                "id" => 102,
                "name" => "Vélo de Propriétaire Petite-Patrie en libre service",
                "position" => "45.540 -73.595",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                "model" => "Vélo",
                "bike_type" => "regular",
                "size" => "big",
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
                "owner_id" => 6,
                "created_at" => "2020-05-01 13:57:14",
                "is_self_service" => true,
            ],
        ];

        foreach ($bikes as $bike) {
            if (!Bike::where("id", $bike["id"])->exists()) {
                Bike::create($bike);
            } else {
                Bike::where("id", $bike["id"])
                    ->first()
                    ->update($bike);
            }
        }

        \DB::statement(
            "SELECT setval('loanables_id_seq'::regclass, (SELECT MAX(id) FROM loanables) + 1)"
        );
    }
}
