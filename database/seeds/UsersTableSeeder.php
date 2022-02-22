<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $solon = [
            "password" => "locomotion",
            "date_of_birth" => "1965-06-04",
            "address" => "6450 Christophe-Colomb, Montréal",
            "postal_code" => "H2S 2G7",
            "phone" => "555 555-5555",
            "accept_conditions" => true,
        ];

        $users = [
            "soutien@locomotion.app" => array_merge($solon, [
                "id" => 1,
                "role" => "admin",
                "name" => "Soutien Locomotion",
            ]),
            "solonahuntsic@locomotion.app" => array_merge($solon, [
                "id" => 2,
                "role" => "admin",
                "name" => "Solon",
                "last_name" => "Ahuntsic",
                "description" => "Propriétaire de la flotte dans Ahuntsic.",
            ]),
            "solonpetitepatrie@locomotion.app" => array_merge($solon, [
                "id" => 3,
                "role" => "admin",
                "name" => "Solon",
                "last_name" => "Petite-Patrie",
                "description" =>
                    "Propriétaire de la flotte dans La Petite-Patrie.",
            ]),
            "proprietaireahuntsic@locomotion.app" => array_merge($solon, [
                "id" => 4,
                "name" => "Propriétaire",
                "last_name" => "Ahuntsic",
                "description" => "Salut tout le monde :)",
                "submitted_at" => new \DateTime(),
            ]),
            "emprunteurahuntsic@locomotion.app" => array_merge($solon, [
                "id" => 5,
                "name" => "Emprunteur",
                "last_name" => "Ahuntsic",
                "submitted_at" => new \DateTime(),
            ]),
            "proprietairepetitepatrie@locomotion.app" => array_merge($solon, [
                "id" => 6,
                "name" => "Propriétaire",
                "last_name" => "Petite-Patrie",
                "description" => "Salut tout le monde :)",
                "submitted_at" => new \DateTime(),
            ]),
            "emprunteurpetitepatrie@locomotion.app" => array_merge($solon, [
                "id" => 7,
                "name" => "Emprunteur",
                "last_name" => "Petite-Patrie",
                "description" => "Salut tout le monde :)",
                "submitted_at" => new \DateTime(),
            ]),
        ];

        // Community memberships
        $memberships = [
            "soutien@locomotion.app" => [],
            "solonahuntsic@locomotion.app" => [
                // 8: Ahuntsic
                8 => ["approved_at" => new \DateTime()],
            ],
            "solonpetitepatrie@locomotion.app" => [
                // 9: Petite-Patrie
                9 => ["approved_at" => new \DateTime()],
            ],
            "proprietaireahuntsic@locomotion.app" => [
                // 8: Ahuntsic
                8 => ["approved_at" => new \DateTime()],
            ],
            "emprunteurahuntsic@locomotion.app" => [
                // 8: Ahuntsic
                8 => ["approved_at" => new \DateTime()],
            ],
            "proprietairepetitepatrie@locomotion.app" => [
                // 9: Petite-Patrie
                9 => ["approved_at" => new \DateTime()],
            ],
            "emprunteurpetitepatrie@locomotion.app" => [
                // 9: Petite-Patrie
                9 => ["approved_at" => new \DateTime()],
            ],
        ];

        foreach ($users as $email => $data) {
            $data = array_merge($data, [
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
