<?php

namespace Tests\Integration;

use App\Models\Invitation;
use App\Models\Community;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;

use App\Mail\Invitation\Created as InvitationCreatedMail;

class InvitationTest extends TestCase
{
    private static $getInvitationResponseStructure = [
        "id",
        "email",
        "token",
        "community_id",
        "for_community_admin",
        "consumed_at",
        "updated_at",
        "created_at",
    ];

    public function testCreateInvitationsWithoutEmail()
    {
        $response = $this->json("POST", "/api/v1/invitations", []);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "email" => ["L'adresse email est requise."],
            ],
        ]);
    }

    public function testCreateInvitationsWithoutCommunity()
    {
        $data = [
            "email" => $this->faker->email,
        ];

        $response = $this->json("POST", "/api/v1/invitations", $data);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "community_id" => ["Le champ community id est obligatoire sauf si for community admin est true."]
            ],
        ]);
    }

    public function testCreateInvitations()
    {
        $this->withoutEvents();
        $community = factory(Community::class)->create();
        $data = [
            "email" => $this->faker->email,
            "community_id" => $community->id,
        ];

        $response = $this->json("POST", "/api/v1/invitations", $data);
        $response->assertStatus(201)
            ->assertJsonStructure(static::$getInvitationResponseStructure)
            ->assertJson([
                "email" => $data['email'],
                "community_id" => $community->id,
            ]);
    }

    public function testAcceptInvitations()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();
        $user = factory(User::class)->create();

        $this->actAs($user);
        $data = [
            "token" => $invitation->token,
        ];
        $response = $this->json("POST", "/api/v1/invitations/accept", $data);
        $response->assertStatus(200)
            ->assertJsonStructure(static::$getInvitationResponseStructure);

        $invitation->refresh();
        $this->assertNotNull($invitation->consumed_at);

        $user->refresh();
        $this->assertEquals($user->communities[0]->id, $invitation->community->id);
    }

    public function testCannotAcceptConsumedInvitations()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();
        $invitation->consume();
        $user = factory(User::class)->create();

        $this->actAs($user);
        $data = [
            "token" => $invitation->token,
        ];
        $response = $this->json("POST", "/api/v1/invitations/accept", $data);
        $response->assertStatus(403);
        $this->assertStringStartsWith("Le code d'invitation a déjà été utilisé", json_decode($response->getContent())->message);
    }

    public function testCannotAcceptInvitationsWithoutToken()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();
        $user = factory(User::class)->create();

        $this->actAs($user);
        $response = $this->json("POST", "/api/v1/invitations/accept", []);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "token" => ["Le champ token est obligatoire."]
            ]
        ]);
    }

    public function testCannotUpdateInvitations()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();

        $data = [
            "email" => $this->faker->email,
        ];

        $response = $this->json("PUT", "/api/v1/invitations/".$invitation->id, $data);
        $response->assertStatus(403)->assertJson([
            "message" => "Impossible de modifer une invitation déjà envoyée."
        ]);
    }

    public function testDeactivateInvitations()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();

        $response = $this->json("DELETE", "/api/v1/invitations/".$invitation->id);
        $response->assertStatus(200)
            ->assertJsonStructure(static::$getInvitationResponseStructure);
        $data = json_decode($response->getContent());
        $this->assertIsString($data->consumed_at);
    }

    public function testCannotDeactivateConsumedInvitations()
    {
        $invitation = factory(Invitation::class)->states("withCommunity")->create();
        $invitation->consume();

        $response = $this->json("DELETE", "/api/v1/invitations/".$invitation->id);
        $response->assertStatus(403)->assertJson([
            "message" => "Impossible de désactiver l'invitation car elle a déjà été utilisée ou désactivée."
        ]);
    }
}
