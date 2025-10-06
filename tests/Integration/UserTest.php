<?php

namespace Tests\Integration;

use App\Events\RegistrationSubmittedEvent;
use App\Models\Community;
use App\Models\File;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Testing\Assert;
use Mockery;
use Stripe;
use Tests\TestCase;
use Carbon\Carbon;

class UserTest extends TestCase
{
    private static $userResponseStructure = [
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

    public function testOrderUsersById()
    {
        $data = [
            "order" => "id",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
        ];
        $response = $this->json("GET", "/api/v1/communities/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderUsersByFullName()
    {
        $data = [
            "order" => "full_name",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderUsersByEmail()
    {
        $data = [
            "order" => "email",
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterUsersById()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "id" => "3",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterUsersByCreatedAt()
    {
        // Lower bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "created_at" => "2020-11-10T01:23:45Z@",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Lower and upper bounds
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "created_at" => "2020-11-10T01:23:45Z@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Upper bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "created_at" => "@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Degenerate case when bounds are removed
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "created_at" => "@",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterUsersByFullName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "full_name" => "Ariane",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterUsersWithQuery()
    {
        $user = factory(User::class)->create();

        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "q" => $user['full_name'],
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure)
            ->assertJson([
                "data" => [[
                    "id" => $user['id'],
                    "full_name" => $user['full_name'],
                ]],
                "total" => 1,
            ]);
    }

    public function testFilterUsersByIsDeactivated()
    {
        // Zero integer
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "is_deactivated" => 0,
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        $notDeactivatedResponseContent = $response->baseResponse->getContent();

        // Positive integer
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "is_deactivated" => 1,
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        $deactivatedResponseContent = $response->baseResponse->getContent();

        // Boolean false
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "is_deactivated" => false,
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        Assert::assertJsonStringEqualsJsonString(
            $notDeactivatedResponseContent,
            $response->baseResponse->getContent()
        );

        // Boolean true
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "is_deactivated" => true,
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        Assert::assertJsonStringEqualsJsonString(
            $deactivatedResponseContent,
            $response->baseResponse->getContent()
        );
    }

    public function testFilterUsersByCommunityId()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "communities.id" => "3",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterUsersByCommunityName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "communities.name" => "Patrie",
        ];
        $response = $this->json("GET", "/api/v1/users/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testCreateUsers()
    {
        $data = [
            "accept_conditions" => true,
            "gdpr" => true,
            "newsletter" => true,
            "name" => $this->faker->name,
            "last_name" => $this->faker->name,
            "email" => $this->faker->unique()->safeEmail,
            "password" =>
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            "description" => null,
            "date_of_birth" => null,
            "address" => "",
            "postal_code" => "",
            "phone" => "",
            "is_smart_phone" => false,
            "other_phone" => "",
            "remember_token" => Str::random(10),
        ];

        $response = $this->json("POST", "/api/v1/users", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                "id",
                "name",
                "last_name",
                "email",
                "description",
                "date_of_birth",
                "address",
                "postal_code",
                "phone",
                "is_smart_phone",
                "other_phone",
                "accept_conditions",
                "gdpr",
                "newsletter",
                "created_at",
                "updated_at",
            ]);
    }

    public function testCreateUsersWithSimilarEmails()
    {
        $user = factory(User::class)
            ->make()
            ->toArray();
        $user["password"] = "12354123124";

        $response = $this->json("POST", "/api/v1/users", $user);
        $response->assertStatus(201);

        $response = $this->json("POST", "/api/v1/users", $user);
        $response->assertStatus(422);

        // Case insensitivity
        $user["email"] =
            strtoupper($user["email"][0]) . substr($user["email"], 1);
        $response = $this->json("POST", "/api/v1/users", $user);
        $response->assertStatus(422);
    }

    public function testShowUsers()
    {
        $user = factory(User::class)->create();
        $data = [];

        $response = $this->json("GET", "/api/v1/users/$user->id", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                "id",
                "name",
                "last_name",
                "email",
                "email_verified_at",
                "description",
                "date_of_birth",
                "address",
                "postal_code",
                "phone",
                "is_smart_phone",
                "other_phone",
                "remember_token",
                "created_at",
                "updated_at",
                "role",
                "full_name",
            ]);
    }

    public function testUpdateUsers()
    {
        $user = factory(User::class)->create();
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateUsersByItself()
    {
        $user = factory(User::class)->create();
        $data = [
            "name" => $this->faker->name,
        ];

        $this->actAs($user);
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUsersCannotUpdateItsCommunityAttributes()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user->communities()->attach($community, ["approved_at" => new Carbon()]);

        $this->actAs($user);
        $response = $this->json("PUT", "/api/v1/users/$user->id", [
            "name" => $this->faker->name,
            "communities" => []
        ])->assertStatus(422);

        $user->refresh();
        $this->assertCount(1, $user->communities);
    }

    public function testUpdateUsersWithNonexistentId()
    {
        $user = factory(User::class)->create();
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json("PUT", "/api/v1/users/208084082084", $data);

        $response->assertStatus(404);
    }

    public function testDeleteUsers()
    {
        $user = factory(User::class)->create();

        $response = $this->json("DELETE", "/api/v1/users/$user->id");

        $response->assertStatus(200);
    }

    public function testDeleteUsersWithNonexistentId()
    {
        $user = factory(User::class)->create();

        $response = $this->json("DELETE", "/api/v1/users/0280398420384");

        $response->assertStatus(404);
    }

    public function testListUsers()
    {
        $users = factory(User::class, 2)
            ->create()
            ->map(function ($user) {
                return $user->only(static::$userResponseStructure);
            });

        $response = $this->json("GET", "/api/v1/users");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                $this->buildCollectionStructure(static::$userResponseStructure)
            );
    }

    public function testAssociateUserToCommunity()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $response = $this->json(
            "PUT",
            "/api/v1/users/$user->id/communities/$community->id"
        );
        $response->assertStatus(200);
    }

    public function testUpdateUserWithCommunity()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $data = [
            "communities" => [["id" => $community->id]],
        ];

        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$userResponseStructure);
    }

    public function testUpdateUserWithProofForNewCommunity_fails()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $proof = factory(File::class)->create([
            "field" => "proof",
        ]);

        $data = [
            "communities" => [
                ["id" => $community->id, "proof" => [["id" => $proof->id]]],
            ],
        ];

        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response->assertStatus(422);
    }

    public function testUpdateUserWithCommunityProof_triggersRegistrationSubmitted()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user->communities()->save($community);

        $proof = factory(File::class)->create([
            "field" => "proof",
        ]);

        $data = [
            "communities" => [
                ["id" => $community->id, "proof" => [["id" => $proof->id]]],
            ],
        ];

        Event::fake([RegistrationSubmittedEvent::class]);
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$userResponseStructure);
        Event::assertDispatched(RegistrationSubmittedEvent::class);
    }

    public function testUpdateUserWithIdenticCommunityProof_doesntTriggerRegistrationSubmitted()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user->communities()->save($community);

        $proof = factory(File::class)->create([
            "field" => "proof",
        ]);
        $user->communities[0]->pivot->proof()->save($proof);

        $data = [
            "communities" => [
                ["id" => $community->id, "proof" => [["id" => $proof->id]]],
            ],
        ];

        Event::fake([RegistrationSubmittedEvent::class]);
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$userResponseStructure);
        Event::assertNotDispatched(RegistrationSubmittedEvent::class);
    }

    public function testUpdateUserWithoutCommunityProof_doesntTriggerRegistrationSubmitted()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user->communities()->save($community);

        $data = [
            "communities" => [["id" => $community->id]],
        ];

        Event::fake([RegistrationSubmittedEvent::class]);
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$userResponseStructure);
        Event::assertNotDispatched(RegistrationSubmittedEvent::class);
    }

    public function testShowUsersCommunities()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $otherCommunity = factory(Community::class)->create();

        $data = [
            "communities" => [["id" => $community->id]],
        ];
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
        $response->assertStatus(200);

        $data = [
            "users.id" => $user->id,
        ];

        $response = $this->json(
            "GET",
            "/api/v1/communities/$otherCommunity->id",
            $data
        );
        $response->assertStatus(404);

        $response = $this->json(
            "GET",
            "/api/v1/communities/$community->id",
            $data
        );
        $response->assertStatus(200);
    }

    public function testListUsersCommunities()
    {
        $user = factory(User::class)->create();
        $communities = factory(Community::class, 2)->create();

        $data = [
            "communities" => [["id" => $communities[0]->id]],
        ];
        $response = $this->json("PUT", "/api/v1/users/$user->id", $data);

        $data = [
            "users.id" => $user->id,
        ];
        $response = $this->json("GET", "/api/v1/communities", $data);
        $response
            ->assertStatus(200)
            ->assertJson(["total" => 1])
            ->assertJsonStructure(
                $this->buildCollectionStructure(
                    static::$getCommunityResponseStructure
                )
            );
    }

    public function testUserCannotPromoteItself()
    {
        $user = factory(User::class)->create();

        foreach( ["admin", "community_admin"] as $role ){
            $data = [
                "role" => $role
            ];
            $this->actAs($user);
            $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
            $response->assertStatus(422);
            $user->refresh();
            $this->assertNull($user->role);
        }
    }

    public function testCommunityAdminCannotPromoteItself()
    {
        $communityAdmin = factory(User::class)->create();
        $communityAdmin->role = "community_admin";
        $communityAdmin->save();

        $data = [
            "role" => "admin"
        ];
        $this->actAs($communityAdmin);
        $response = $this->json("PUT", "/api/v1/users/$communityAdmin->id", $data);
        $response->assertStatus(422);
        $communityAdmin->refresh();
        $this->assertEquals($communityAdmin->role, "community_admin");
    }

    public function testAdminCanPromoteUser()
    {
        $user = factory(User::class)->create();

        foreach( ["admin", "community_admin"] as $role ){
            $data = [
                "role" => $role
            ];
            $response = $this->json("PUT", "/api/v1/users/$user->id", $data);
            $response->assertStatus(200);
            $user->refresh();
            $this->assertEquals($user->role, $role);
        }
    }

    public function testCommunityUserCannotPromoteItselfAsResponsible()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user->communities()->attach($community, ["approved_at" => new Carbon()]);

        $this->actAs($user);
        $response = $this->json("PUT", "/api/v1/communities/$community->id/users/$user->id", [
            "communities" => [[
                "id" => $community->id,
                "role" => "responsible"
            ]],
        ]);
        $response->assertStatus(403);
        $user->refresh();
        $this->assertNotEquals($user->communities[0]->pivot->role, "responsible");
    }

    public function testCommunityResponsibleCannotDemoteItself()
    {
        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $user->communities()->attach($community, [
            "approved_at" => new Carbon(),
            "role" => "responsible"
        ]);
        $user->refresh();
        $this->assertEquals($user->communities[0]->pivot->role, "responsible");

        $this->actAs($user);
        $response = $this->json("PUT", "/api/v1/communities/$community->id/users/$user->id", [
            "communities" => [[
                "id" => $community->id,
                "role" => null,
            ]],
        ]);
        $response->assertStatus(403);
        $user->refresh();
        $this->assertEquals($user->communities[0]->pivot->role, "responsible");
    }

    public function testCommunityResponsibleCanPromoteOther()
    {
        $user = factory(User::class)->create();
        $responsibleUser = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $user->communities()->attach($community, [
            "approved_at" => new Carbon(),
        ]);
        $responsibleUser->communities()->attach($community, [
            "approved_at" => new Carbon(),
            "role" => "responsible"
        ]);

        $this->actAs($responsibleUser);
        $response = $this->json("PUT", "/api/v1/communities/$community->id/users/$user->id/promote", [
            "role" => "responsible",
        ]);
        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals($user->communities[0]->pivot->role, "responsible");
    }

    public function testUserCannotPromoteOther()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $community = factory(Community::class)->create();

        $user->communities()->attach($community, [
            "approved_at" => new Carbon(),
        ]);
        $otherUser->communities()->attach($community, [
            "approved_at" => new Carbon(),
        ]);

        $this->actAs($user);
        $response = $this->json("PUT", "/api/v1/communities/$community->id/users/$otherUser->id/promote", [
            "role" => "responsible",
        ]);
        $response->assertForbidden();
        $otherUser->refresh();
        $this->assertNotEquals($otherUser->communities[0]->pivot->role, "responsible");
    }

}
