<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $molotov = [
            "password" => "locomotion",
            "date_of_birth" => "2009-01-01",
            "address" => "2065 rue Parthenais",
            "postal_code" => "H2K 3T1",
            "phone" => "514-908-9744",
            "description" => "Communications alternatives",
        ];

        $solon = [
            "password" => "locomotion",
            "date_of_birth" => "2018-06-04",
            "address" => "6450 Christophe-Colomb, Montréal",
            "postal_code" => "H2S 2G7",
            "phone" => "555 555-5555",
            "accept_conditions" => true,
        ];

        $users = [
            "soutien@locomotion.app" => array_merge(
                [
                    "id" => 1,
                    "role" => "admin",
                    "name" => "Soutien Locomotion",
                ],
                $molotov
            ),
            "emile@locomotion.app" => array_merge($molotov, [
                "id" => 2,
                "name" => "Émile",
                "last_name" => "Plourde-Lavoie",
                "description" => "Salut tout le monde :)",
                "submitted_at" => new \DateTime(),
            ]),
            "ariane@locomotion.app" => array_merge($molotov, [
                "id" => 3,
                "name" => "Ariane",
                "last_name" => "Mercier",
                "submitted_at" => new \DateTime(),
            ]),
            "proprietairevoiture@locomotion.app" => array_merge($solon, [
                "id" => 4,
                "name" => "Propriétaire",
                "last_name" => "Voiture",
                "description" => "Sympatique propriétaire de voiture.",
                "date_of_birth" => "1984-08-21",
            ]),
            "emprunteurvoiture@locomotion.app" => array_merge($solon, [
                "id" => 5,
                "name" => "Emprunteur",
                "last_name" => "Voiture",
                "description" => "Emprunteur de voiture prudent.",
                "date_of_birth" => "1990-05-11",
            ]),
        ];

        // Community memberships
        $memberships = [
            "soutien@locomotion.app" => [],
            "emile@locomotion.app" => [
                // 1: Bellechasse
                1 => [
                    "role" => "admin",
                    "approved_at" => new \DateTime(),
                ],
            ],
            "ariane@locomotion.app" => [
                // 1: Bellechasse
                1 => [
                    "approved_at" => new \DateTime(),
                ],
            ],
            "proprietairevoiture@locomotion.app" => [
                // 9: Petite-Patrie
                9 => [
                    "approved_at" => new \DateTime(),
                ],
            ],
            "emprunteurvoiture@locomotion.app" => [
                // 9: Petite-Patrie
                9 => [
                    "approved_at" => new \DateTime(),
                ],
            ],
        ];

        $id = 1;
        foreach ($users as $email => $data) {
            $data = array_merge($data, [
                "id" => $id,
                "email" => $email,
                "password" => Hash::make(
                    array_get($data, "password", "password")
                ),
            ]);

            if (!User::where("email", $email)->exists()) {
                User::create($data);
            } else {
                User::where("email", $email)->update($data);
            }

            $id += 1;
        }

        foreach ($memberships as $email => $communities) {
            $user = User::where("email", $email)->first();

            User::withoutEvents(function () use ($user, $communities) {
                $user->communities()->sync($communities);
            });
        }

        \DB::statement(
            "SELECT setval('users_id_seq'::regclass, (SELECT MAX(id) FROM users) + 1)"
        );
    }
}
