<?php

use App\Models\Community;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CommunitiesTableSeeder extends Seeder
{
    public function run() {
        $communities = [
            [
                'id' => 1,
                'name' => 'Rosemont',
                'description' => 'La communauté extraordinaire des bonnes gens de Rosemont!',
                'area' => '0103000020E61000000100000006000000EF94FEB1616652C0D06AA6361CC546403EA57FECA36652C00F983F9886C4464036D1F1F33A6652C0F18A676930C4464097B0EFFE146652C0FD8EF1EC77C4464077D5DB3A4C6652C067DA1811A9C44640EF94FEB1616652C0D06AA6361CC54640'
            ],
        ];

        foreach ($communities as $community) {
            if (!Community::where('id', $community['id'])->exists()) {
                Community::create($community);
            } else {
                Community::where('id', $community['id'])->update($community);
            }
        }
    }
}
