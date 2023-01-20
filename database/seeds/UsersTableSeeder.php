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
            "date_of_birth" => "1981-07-19",
            "address" => "61 boulevard Marie et Alexandre Oyon, Le Mans",
            "postal_code" => "72000",
            "phone" => "06 20 20 54 97",
            "accept_conditions" => true,
            "gdpr" => true,
            "newsletter" => true,
        ];

        $users = [
            "dev@colocauto.org" => array_merge($solon, [
                "id" => 1,
                "role" => "admin",
                "name" => "Soutien Colocauto",
            ]),
            "pauline@colocauto.org" => array_merge($solon, [
                "id" => 2,
                "name" => "Pauline",
                "last_name" => "Bonneau",
                "description" => "",
            ]),
            "isabelle@colocauto.org" => array_merge($solon, [
                "id" => 3,
                "name" => "Isabelle",
                "last_name" => "Deschamps",
                "description" => "",
            ]),
            "stephanie@colocauto.org" => array_merge($solon, [
                "id" => 4,
                "name" => "Stéphanie",
                "last_name" => "Pineau",
                "description" => "",
            ]),
            "joseph@colocauto.org" => array_merge($solon, [
                "id" => 5,
                "name" => "Joseph",
                "last_name" => "Schmitt",
                "description" => "",
            ]),
            "suzanne@colocauto.org" => array_merge($solon, [
                "id" => 6,
                "name" => "Suzanne",
                "last_name" => "Chauvet",
                "description" => "",
            ]),
            "camille@colocauto.org" => array_merge($solon, [
                "id" => 7,
                "name" => "Camille",
                "last_name" => "Dos Santos",
                "description" => "",
            ]),
            "matthieu@colocauto.org" => array_merge($solon, [
                "id" => 8,
                "name" => "Matthieu",
                "last_name" => "Arnaud",
                "description" => "",
            ]),
            "theophile@colocauto.org" => array_merge($solon, [
                "id" => 9,
                "name" => "Théophile",
                "last_name" => "Devaux",
                "description" => "",
            ]),
            "marcelle@colocauto.org" => array_merge($solon, [
                "id" => 10,
                "name" => "Marcelle",
                "last_name" => "Nicolas",
                "description" => "",
            ]),
            "lucas@colocauto.org" => array_merge($solon, [
                "id" => 11,
                "name" => "Lucas ",
                "last_name" => "Ferrand",
                "description" => "",
            ]),
            "henriette@colocauto.org" => array_merge($solon, [
                "id" => 12,
                "name" => "Henriette",
                "last_name" => "Schneider",
                "description" => "",
            ]),
            "christiane@colocauto.org" => array_merge($solon, [
                "id" => 13,
                "name" => "Christiane",
                "last_name" => "Girard",
                "description" => "",
            ]),
            "alice@colocauto.org" => array_merge($solon, [
                "id" => 14,
                "name" => "Alice",
                "last_name" => "Dupont",
                "description" => "",
            ]),
            "bob@colocauto.org" => array_merge($solon, [
                "id" => 15,
                "name" => "Bob",
                "last_name" => "Martin",
                "description" => "",
            ]),
            "carole@colocauto.org" => array_merge($solon, [
                "id" => 16,
                "name" => "Carole",
                "last_name" => "Deschamps",
                "description" => "",
            ]),
            "david@colocauto.org" => array_merge($solon, [
                "id" => 17,
                "name" => "David",
                "last_name" => "Bocher",
                "description" => "",
            ]),
        ];

        // Community memberships
        $memberships = [
            "dev@colocauto.org" => [],
            "pauline@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-12-06')],
            ],
            "isabelle@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-12-06')],
            ],
            "stephanie@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-11-25')],
            ],
            "joseph@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-11-25')],
            ],
            "suzanne@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-10-01')],
            ],
            "camille@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime('2022-11-25')],
            ],
            "matthieu@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime()],
            ],
            "theophile@colocauto.org" => [
                // 1: Asso Eolien
                1 => ["approved_at" => new \DateTime()],
            ],
            "marcelle@colocauto.org" => [
                // 2: Groupe Citoyen
                2 => ["approved_at" => new \DateTime('2022-05-01')],
            ],
            "lucas@colocauto.org" => [
                // 2: Groupe Citoyen
                2 => ["approved_at" => new \DateTime('2022-05-01')],
            ],
            "henriette@colocauto.org" => [
                // 2: Groupe Citoyen
                2 => ["approved_at" => new \DateTime('2022-05-01')],
            ],
            "christiane@colocauto.org" => [
                // 2: Groupe Citoyen
                2 => ["approved_at" => new \DateTime('2022-05-01')],
            ],
            "alice@colocauto.org" => [
                // 3: Calcul équilibre
                3 => ["approved_at" => new \DateTime('2022-12-15')],
            ],
            "bob@colocauto.org" => [
                // 3: Calcul équilibre
                3 => ["approved_at" => new \DateTime('2022-12-15')],
            ],
            "carole@colocauto.org" => [
                // 3: Calcul équilibre
                3 => ["approved_at" => new \DateTime('2022-12-15')],
            ],
            "david@colocauto.org" => [
                // 3: Calcul équilibre
                3 => ["approved_at" => new \DateTime('2022-12-15')],
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
