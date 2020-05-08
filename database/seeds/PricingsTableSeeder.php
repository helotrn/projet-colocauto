<?php

use App\Models\Pricing;
use Illuminate\Database\Seeder;

class PricingsTableSeeder extends Seeder
{
    public function run() {
        $pricings = [
            [
                'id' => 1,
                'name' => 'Tarification par paliers avec assurances',
                'object_type' => 'bike',
                'rule' => <<<RULE
SI \$MINUTES > 20 ALORS [\$MINUTES * 2, 2]
SI \$MINUTES > 10 ALORS [\$MINUTES * 3, 3]
[\$MINUTES * 4, 4]
RULE
                ,

                'community_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Équation avec 10% de frais et assurances',
                'object_type' => 'car',
                'rule' => <<<RULE
[(\$MINUTES * 0.25 + \$KM * 0.5) * 1.10, \$KM * 0.01]
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
