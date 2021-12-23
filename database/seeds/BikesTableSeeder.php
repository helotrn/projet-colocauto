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
                "name" => "Le vélo tandem d'Émile",
                "position" => "45.530704 -73.578799",
                "location_description" => "Accrochée sur la clôture",
                "comments" => "Rien à dire de particulier.",
                "instructions" => "Le guidon est lousse un petit peu...",
                "model" => "Minelli Ferrari De Vinci",
                "bike_type" => "regular",
                "size" => "big",
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
                "owner_id" => 1,
                "created_at" => "2020-05-01 13:57:14",
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
