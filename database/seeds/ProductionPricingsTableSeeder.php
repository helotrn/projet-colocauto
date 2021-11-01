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
# Retourne un tableau de deux éléments : [tarif, assurances]

# Réservation de grandes voitures pour 6 blocs de 2 heures ou moins.
SI \$OBJET.pricing_category == 'large' && \$MINUTES <= 6 * 120 ALORS [0.18 * \$KM + 34.12 * PLAFOND(\$MINUTES/120) / 6, 3 + \$SURCOUT_ASSURANCE]
# Réservation de grandes voitures pour plus de 6 blocs.
SI \$OBJET.pricing_category == 'large' ALORS [0.18 * \$KM + 34.12 * (1 + (PLAFOND(\$MINUTES/120) - 6) / 12), \$EMPRUNT.days * (5 + \$SURCOUT_ASSURANCE)]

# Réservation de petites voitures pour 6 blocs de 2 heures ou moins.
SI \$OBJET.pricing_category == 'small' && \$MINUTES <= 6 * 120 ALORS [0.13 * \$KM + 21.81 * PLAFOND(\$MINUTES/120) / 6, 3 + \$SURCOUT_ASSURANCE]
# Réservation de petites voitures pour plus de 6 blocs.
SI \$OBJET.pricing_category == 'small' ALORS [0.13 * \$KM + 21.81 * (1 + (PLAFOND(\$MINUTES/120) - 6) / 12), \$EMPRUNT.days * (5 + \$SURCOUT_ASSURANCE)]

# Tous les autres cas, mais il n'y en a pas d'autres normalement.
[0.13 * \$KM + 21.81 * (1 + PLAFOND(\$MINUTES/120) / 12), \$EMPRUNT.days * (5 + 1)]
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
