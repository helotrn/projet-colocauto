<?php

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarsTableSeeder extends Seeder
{
    public function run()
    {
        // Start cars at 1001
        $cars = [
            [
                "id" => 1001,
                "name" => "Auto de Propriétaire Petite-Patrie sur demande",
                "position" => "45.540, -73.590",
                "location_description" => "Stationnée devant la maison.",
                "comments" => "",
                "instructions" => "",
                // proprietairepetitepatrie@locomotion.app
                "owner_id" => 6,
                "brand" => "Toyota",
                "model" => "Matrix",
                "year_of_circulation" => "2015",
                "transmission_mode" => "automatic",
                "engine" => "fuel",
                "plate_number" => "F123456",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Assurancetourix",
                "has_informed_insurer" => true,
                "created_at" => new \DateTime(),
                "availability_mode" => "never",
                "availability_json" => <<<JSON
[
  {
    "available":true,
    "type":"weekdays",
    "scope":["MO","TU","TH","WE","FR"],
    "period":"00:00-23:59"
  }
]
JSON
                ,
                "pricing_category" => "large",
            ],
        ];

        foreach ($cars as $car) {
            if (!Car::where("id", $car["id"])->exists()) {
                Car::create($car);
            } else {
                Car::where("id", $car["id"])
                    ->first()
                    ->update($car);
            }
        }

        \DB::statement(
            "SELECT setval('loanables_id_seq'::regclass, (SELECT MAX(id) FROM loanables) + 1)"
        );
    }
}
