<?php

use App\Models\Community;
use Illuminate\Database\Seeder;

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
            [
                'id' => 2,
                'name' => 'Rosemont-Est',
                'description' => "Un peu plus à l'est.",
                'area' => '0103000020E61000000100000007000000B19864DC2E6652C03D20CD2787C54640577B2B6DD86552C09159B4C037C546406E2E6AC0A16552C0963B6A43B0C546400BA938BAC46552C09C9CC4BACAC54640B67BAA03D66552C05EF8FC7EA0C546402A73417E0D6652C0C339A17CD1C54640B19864DC2E6652C03D20CD2787C54640'
            ],
            [
                'id' => 3,
                'name' => 'Saint-Laurent',
                'description' => "Carrément dans l'ouest.",
                'area' => '0103000020E6100000010000000B000000147A70C3956B52C0157E50225EC34640418467FC9B6B52C022ACD0F047C34640F6FE7B16AE6B52C0BDBAFD5A46C34640E464C22BDE6B52C067A93A3002C346406832CFA1946B52C08B44E607A7C246400A47FDCA846B52C0F58E8730C1C24640FE65C2086D6B52C04A124011A3C246405E332FA3316B52C0104FA139FEC24640981E619E4F6B52C087D9061A21C34640EBFFDB174B6B52C0EE71135533C34640147A70C3956B52C0157E50225EC34640'
            ]
        ];

        foreach ($communities as $community) {
            if (!Community::where('id', $community['id'])->exists()) {
                Community::create($community);
            } else {
                Community::where('id', $community['id'])->first()->update($community);
            }
        }
    }
}
