<?php

use App\Models\Owner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnersTableSeeder extends Seeder
{
    public function run() {
        $owners = [
            [
                'id' => 1,
                'user_id' => 2,
                'submitted_at' => new DateTime,
            ],
        ];

        foreach ($owners as $owner) {
            if (!Owner::where('id', $owner{'id'})->exists()) {
                Owner::create($owner);
            } else {
                Owner::where('id', $owner['id'])->update($owner);
            }
        }
    }
}
