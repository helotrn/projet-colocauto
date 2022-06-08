<?php

namespace Tests\Integration;

use App\Models\Community;
use App\Models\User;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    private static $getCommunitiesResponseStructure = [
        "current_page",
        "data",
        "first_page_url",
        "from",
        "last_page",
        "last_page_url",
        "next_page_url",
        "path",
        "per_page",
        "prev_page_url",
        "to",
        "total",
    ];

    private static $getUserResponseStructure = [
        "id",
        "name",
        "email",
        "email_verified_at",
        "description",
        "date_of_birth",
        "address",
        "postal_code",
        "phone",
        "is_smart_phone",
        "other_phone",
    ];

    private static $getCommunityResponseStructure = [
        "id",
        "name",
        "description",
        "area",
        "created_at",
        "updated_at",
        "deleted_at",
        "type",
        "center",
    ];

    public function testOrderCommunitiesById()
    {
        $data = [
            "order" => "id",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testOrderCommunitiesByName()
    {
        $data = [
            "order" => "name",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testOrderCommunitiesByParentName()
    {
        $data = [
            "order" => "parent.name",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testOrderCommunitiesByType()
    {
        $data = [
            "order" => "type",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testOrderCommunitiesByUsersCount()
    {
        $community1 = factory(Community::class)->create();
        $community2 = factory(Community::class)->create();

        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();

        $user1
            ->communities()
            ->attach($community2, ["approved_at" => new \DateTime()]);
        $user2
            ->communities()
            ->attach($community2, ["approved_at" => new \DateTime()]);
        $user3
            ->communities()
            ->attach($community1, ["approved_at" => new \DateTime()]);

        $data = [
            "order" => "users_count",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name,users_count",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    ["id" => $community1->id],
                    ["id" => $community2->id],
                ],
            ])
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);

        $data["order"] = "-users_count";
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response->assertStatus(200)->assertJson([
            "data" => [["id" => $community2->id], ["id" => $community1->id]],
        ]);
    }

    public function testFilterCommunitiesById()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "id" => "4",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testFilterCommunitiesByName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "name" => "Patrie",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testFilterCommunitiesByParentId()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "parent.id" => "9",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testFilterCommunitiesByParentName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "parent.name" => "Patrie",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getCommunitiesResponseStructure);
    }

    public function testCreateCommunities()
    {
        $data = [
            "name" => $this->faker->name,
            "description" => $this->faker->sentence,
            "area" => null,
        ];

        $response = $this->json("POST", "/api/v1/communities/", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                "id",
                "name",
                "description",
                "area",
                "updated_at",
                "created_at",
            ]);
    }

    public function testCreateCommunitiesWithNullName()
    {
        $data = [
            "name" => null,
            "description" => $this->faker->sentence,
            "area" => null,
        ];

        $response = $this->json("POST", "/api/v1/communities/", $data);
        $response->assertStatus(422);
    }

    public function testCreateCommunitiesEmptyArea()
    {
        // Inspecting API call showed empty area to be an
        // empty array, not null which used to cause a
        // problem.
        $data = [
            "area" => [],

            "chat_group_url" => "",
            "description" => "Test quartier",
            "long_description" => "<p>Blabla</p>",
            "name" => "Quartier",
            "pricings" => [
                ["name" => "Test", "object_type" => null, "rule" => "0"],
            ],
            "type" => "borough",
        ];

        $response = $this->json("POST", "/api/v1/communities/", $data);
        $response->assertStatus(201);
    }

    public function testShowCommunities()
    {
        $community = factory(Community::class)->create();

        $response = $this->json(
            "GET",
            "/api/v1/communities/$community->id",
            []
        );
        $response->assertStatus(200);
    }

    public function testUpdateCommunities()
    {
        $community = factory(Community::class)->create();
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json(
            "PUT",
            "/api/v1/communities/$community->id",
            $data
        );
        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateCommunitiesByAdminOfCommunity()
    {
        $community = factory(Community::class)->create();

        $this->user->role = "";
        $this->user->save();

        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json(
            "PUT",
            route("communities.update", $community->id),
            $data
        );
        $response->assertStatus(403);

        $communities = [
            $community->id => [
                "role" => "admin",
            ],
        ];
        $this->user->communities()->sync($communities);

        $response = $this->json(
            "PUT",
            route("communities.update", $community->id),
            $data
        );
        $response->assertStatus(200);
    }

    public function testUpdateCommunitiesByNonAdmin()
    {
        $this->user->role = "";
        $community = factory(Community::class)->create();
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json(
            "PUT",
            route("communities.update", $community->id),
            $data
        );

        $response->assertStatus(403);
    }

    public function testDeleteCommunities()
    {
        $community = factory(Community::class)->create();

        $response = $this->json(
            "DELETE",
            route("communities.destroy", $community->id)
        );

        $response->assertStatus(200);
    }

    public function testDeleteCommunitiesByNonAdmin()
    {
        $this->user->role = "";
        $community = factory(Community::class)->create();

        $response = $this->json(
            "DELETE",
            route("communities.destroy", $community->id)
        );

        $response->assertStatus(403);
    }

    public function testListCommunities()
    {
        $communities = factory(Community::class, 2)
            ->create()
            ->map(function ($post) {
                return $post->only(["id", "name", "description", "area"]);
            });

        $response = $this->json("GET", "/api/v1/communities", []);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                $this->buildCollectionStructure([
                    "id",
                    "name",
                    "description",
                    "area",
                ])
            );
    }

    public function testShowCommunitiesUsers()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $data = [
            "users" => [["id" => $user["id"]]],
        ];
        $response = $this->json(
            "PUT",
            "/api/v1/communities/$community->id",
            $data
        );

        $data = [
            "community.id" => $community->id,
        ];
        $response = $this->json("GET", "/api/v1/users", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                $this->buildCollectionStructure(
                    static::$getUserResponseStructure
                )
            );
    }

    public function testCommunityWithParent()
    {
        $community = factory(Community::class)->create();
        $borough = factory(Community::class)->create([
            "type" => "borough",
        ]);
        $subBorough = factory(Community::class)->create([
            "type" => "borough",
            "parent_id" => $borough->id,
        ]);

        $newCommunity = factory(Community::class)->make();

        // Parent doest not exists
        $newCommunity->parent_id = $community->id - 1;
        $this->json(
            "POST",
            route("communities.create"),
            $newCommunity->toArray()
        )
            ->assertStatus(422)
            ->assertJsonStructure([
                "errors" => ["parent_id"],
            ]);

        // Parent is not a borough
        $newCommunity->parent_id = $community->id;
        $this->json(
            "POST",
            route("communities.create"),
            $newCommunity->toArray()
        )
            ->assertStatus(422)
            ->assertJsonStructure([
                "errors" => ["parent_id"],
            ]);

        // Parent can be null
        $newCommunity->parent_id = null;
        $this->json(
            "POST",
            route("communities.create"),
            $newCommunity->toArray()
        )->assertStatus(201);

        // Parent is valid
        $newCommunity->parent_id = $borough->id;
        $response = $this->json(
            "POST",
            route("communities.create"),
            $newCommunity->toArray()
        )->assertStatus(201);

        // Parent cannot be self
        $data = json_decode($response->getContent());
        $data->parent_id = $data->id;
        $response = $this->json(
            "PUT",
            route("communities.update", $data->id),
            (array) $data
        )
            ->assertStatus(422)
            ->assertJsonStructure([
                "errors" => ["parent_id"],
            ]);

        // Parent cannot create loops
        $borough->parent_id = $subBorough->id;
        $this->json(
            "PUT",
            route("communities.update", $borough->id),
            (array) $borough->toArray()
        )
            ->assertStatus(422)
            ->assertJsonStructure([
                "errors" => ["parent_id"],
            ]);
    }
}
