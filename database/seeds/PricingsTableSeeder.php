<?php

use App\Models\Pricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PricingsTableSeeder extends Seeder
{
    public function run() {
        $pricings = [
            [
                'id' => 1,
                'name' => 'Tarification régressive par paliers',
                'object_type' => 'bike',
                'rule' => <<<RULE
SI \$KM > 20 ALORS \$KM * 2
SI \$KM > 10 ALORS \$KM * 3
\$KM * 4
RULE
                ,

                'community_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Équation avec 10% de frais',
                'object_type' => 'car',
                'rule' => <<<RULE
(\$MINUTES * 0.25 + \$KM * 0.5) * 1.10
RULE
                ,
                'community_id' => 1,
            ],
            [
                'id' => 3,
                'name' => '5$ en toute circonstances',
                'object_type' => 'trailer',
                'rule' => '5',
                'community_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Tarification par défaut',
                'object_type' => null,
                'rule' => '20',
                'community_id' => 1,
            ],
        ];

        foreach ($pricings as $pricing) {
            if (!Pricing::where('id', $pricing['id'])->exists()) {
                Pricing::create($pricing);
            } else {
                Pricing::where('id', $pricing['id'])->update($pricing);
            }
        }

        \DB::statement("SELECT setval('pricings_id_seq'::regclass, (SELECT MAX(id) FROM pricings) + 1)");
    }
}
