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
                'name' => 'bike 24h',
                'object_type' => 'App\Models\Bike',
                'rule' => '',
                'community_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'car 24h',
                'object_type' => 'App\Models\Car',
                'rule' => '',
                'community_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'car 100km',
                'object_type' => 'App\Models\Car',
                'rule' => '',
                'community_id' => 2,
            ],
            [
                'id' => 4,
                'name' => 'trailer 24h',
                'object_type' => 'App\Models\Trailer',
                'rule' => '',
                'community_id' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Tarification par défaut',
                'object_type' => null,
                'rule' => '20',
                'community_id' => 1,
            ],
        ];

        foreach ($pricings as $pricing) {
            if (!Pricing::where('id', $pricing{'id'})->exists()) {
                Pricing::create($pricing);
            } else {
                Pricing::where('id', $pricing['id'])->update($pricing);
            }
        }

        \DB::statement("SELECT setval('pricings_id_seq'::regclass, 6)");
    }
}
