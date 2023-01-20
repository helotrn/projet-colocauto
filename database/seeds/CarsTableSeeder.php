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
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.35,
                "cost_per_month" => 100,
                "owner_compensation" => 80,
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
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.2,
                "cost_per_month" => 80,
                "owner_compensation" => 50,
            ],
            [
                "id" => 1004,
                "name" => "Renault de Marcelle",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // Marcelle
                "owner_id" => 10,
                // POC - Groupe Citoyen
                "community_id" => 2,
                "brand" => "Renault",
                "model" => "Twingo",
                "year_of_circulation" => "2010",
                "transmission_mode" => "manual",
                "engine" => "fuel",
                "plate_number" => "T123457",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.25,
                "cost_per_month" => 70,
                "owner_compensation" => 30,
            ],
            [
                "id" => 1005,
                "name" => "Peugeot de Christiane",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // Christiane
                "owner_id" => 13,
                // POC - Groupe Citoyen
                "community_id" => 2,
                "brand" => "Peugeot",
                "model" => "307 SW",
                "year_of_circulation" => "2010",
                "transmission_mode" => "manual",
                "engine" => "fuel",
                "plate_number" => "T123458",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.3,
                "cost_per_month" => 80,
                "owner_compensation" => 50,
            ],
            [
                "id" => 1006,
                "name" => "Toyota de Camille",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // Camille
                "owner_id" => 7,
                // POC - Asso Eolien
                "community_id" => 1,
                "brand" => "Toyota",
                "model" => "Corolla",
                "year_of_circulation" => "2010",
                "transmission_mode" => "manual",
                "engine" => "fuel",
                "plate_number" => "T123459",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.3,
                "cost_per_month" => 100,
                "owner_compensation" => 80,
            ],
            [
                "id" => 1007,
                "name" => "Zoé d'Atout Vent",
                "location_description" => "",
                "comments" => "",
                "instructions" => "",
                // no owner
                // POC - Asso Eolien
                "community_id" => 1,
                "brand" => "Renault",
                "model" => "Zoé",
                "year_of_circulation" => "2010",
                "transmission_mode" => "manual",
                "engine" => "fuel",
                "plate_number" => "T123460",
                "is_value_over_fifty_thousand" => "false",
                "papers_location" => "in_the_car",
                "insurer" => "Macif",
                "created_at" => new \DateTime(),
                "availability_mode" => "always",
                "availability_json" => <<<JSON
[]
JSON
                ,
                "pricing_category" => "large",
                "cost_per_km" => 0.15,
                "cost_per_month" => 80,
                "owner_compensation" => 0,
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
