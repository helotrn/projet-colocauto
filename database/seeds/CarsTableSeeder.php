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
                "location_description" => "Stationnée devant la maison.",
                "comments" => "",
                "instructions" => "",
                // pauline
                "owner_id" => 2,
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
    "period":"00:00-24:00"
  }
]
JSON
                ,
                "pricing_category" => "large",
            ],
            [
                "id" => 1002,
                "name" => "Voiture 1",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // Alice
                "owner_id" => 14,
                "brand" => "Renault",
                "model" => "Megane",
                "year_of_circulation" => "2015",
                "transmission_mode" => "automatic",
                "engine" => "fuel",
                "plate_number" => "F123456",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "has_informed_insurer" => true,
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => "",
                "pricing_category" => "large",
                "cost_per_km" => 0.35,
                "cost_per_month" => 100,
            ],
            [
                "id" => 1003,
                "name" => "Voiture 2",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // David
                "owner_id" => 17,
                "brand" => "Renault",
                "model" => "Twingo",
                "year_of_circulation" => "2010",
                "transmission_mode" => "manual",
                "engine" => "fuel",
                "plate_number" => "T123456",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "has_informed_insurer" => true,
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => "",
                "pricing_category" => "large",
                "cost_per_km" => 0.2,
                "cost_per_month" => 80,
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
