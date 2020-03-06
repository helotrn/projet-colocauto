<?php

use App\Models\Bike;
use Illuminate\Database\Seeder;

class BikesTableSeeder extends Seeder
{
    public function run() {
        $bikes = [
            [
                'id' => 1,
                'name' => "Le vélo tandem d'Émile",
                'position' => "45.530704 -73.578799",
                'location_description' => 'Accrochée sur la clôture',
                'comments' => 'Rien à dire de particulier.',
                'instructions' => 'Le guidon est lousse un petit peu...',
                'model' => 'Minelli Ferrari De Vinci',
                'bike_type' => 'regular',
                'size' => 'big',
                'availability_ics' => '',
                'owner_id' => 1,
            ],
        ];

        foreach ($bikes as $bike) {
            if (!Bike::where('id', $bike['id'])->exists()) {
                Bike::create($bike);
            } else {
                Bike::where('id', $bike['id'])->first()->update($bike);
            }
        }

        \DB::statement("SELECT setval('loanables_id_seq'::regclass, (SELECT MAX(id) FROM loanables) + 1)");
    }
}
