<?php

use App\Models\Community;
use App\Models\Pricing;
use Illuminate\Database\Seeder;

class ProductionPricingsTableSeeder extends Seeder
{
    public function run()
    {
        $pricings = [
            [
                "name" => "Tarif Voitures Colocauto",
                "object_type" => "car",
                "rule" => <<<RULE
# Le coût ne dépend que du nombre de kilomètres et du tarif du véhicule
\$OBJET.cost_per_km * \$KM
RULE
            ,
            ],
        ];

        foreach (Community::where("id", "<=", 10)->get() as $community) {
            foreach ($pricings as $data) {
                $pricing = new Pricing();
                $pricing->fill($data);
                $pricing->community()->associate($community);
                $pricing->save();
            }
        }

        \DB::statement(
            "SELECT setval('pricings_id_seq'::regclass, (SELECT MAX(id) FROM pricings) + 1)"
        );
    }
}
