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
                "name" => "Emprunt gratuit",
                "object_type" => "trailer",
                "rule" => "0",
            ],
            [
                "name" => "Emprunt gratuit",
                "object_type" => "bike",
                "rule" => "0",
            ],
            [
                "name" => "Tarif Voitures LocoMotion",
                "object_type" => "car",
                "rule" => <<<RULE
SI \$OBJET.pricing_category == 'large' && PLAFOND(\$MINUTES/120) <= 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation > 3 ALORS [0.18 * \$KM + 34.12 / 6 * PLAFOND(\$MINUTES/120), 3 + 0]
SI \$OBJET.pricing_category == 'large' &&  PLAFOND(\$MINUTES/120) <= 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation <= 3 ALORS [0.18 * \$KM + 34.12 / 6 * PLAFOND(\$MINUTES/120), 3 + 1]
SI \$OBJET.pricing_category == 'large' &&  PLAFOND(\$MINUTES/120) > 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation > 3 ALORS [0.18 * \$KM + 34.12 + 34.12 / 12 * (PLAFOND(\$MINUTES/120) - 6), \$EMPRUNT.days * (5 + 0)]
SI \$OBJET.pricing_category == 'large' &&  PLAFOND(\$MINUTES/120) > 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation <= 3 ALORS [0.18 * \$KM + 34.12 + 34.12 / 12 * (PLAFOND(\$MINUTES/120) - 6), \$EMPRUNT.days * (5 + 1)]
SI \$OBJET.pricing_category == 'small' && PLAFOND(\$MINUTES/120) <= 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation > 3 ALORS [0.13 * \$KM + 21.81 / 6 * PLAFOND(\$MINUTES/120), 3 + 0]
SI \$OBJET.pricing_category == 'small' &&  PLAFOND(\$MINUTES/120) <= 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation <= 3 ALORS [0.13 * \$KM + 21.81 / 6 * PLAFOND(\$MINUTES/120), 3 + 1]
SI \$OBJET.pricing_category == 'small' &&  PLAFOND(\$MINUTES/120) > 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation > 3 ALORS [0.13 * \$KM + 21.81 + 21.81 / 12 * (PLAFOND(\$MINUTES/120) - 6), \$EMPRUNT.days * (5 + 0)]
SI \$OBJET.pricing_category == 'small' &&  PLAFOND(\$MINUTES/120) > 6 && \$EMPRUNT.start.year - \$OBJET.year_of_circulation <= 3 ALORS [0.13 * \$KM + 21.81 + 21.81 / 12 * (PLAFOND(\$MINUTES/120) - 6), \$EMPRUNT.days * (5 + 1)]
[0.13 * \$KM + 21.81 + 21.81 / 12 * PLAFOND(\$MINUTES/120), \$EMPRUNT.days * (5 + 1)]
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
