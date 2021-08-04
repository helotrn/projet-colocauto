<?php

namespace Tests\Unit\Mail\Borrower;

use App\Mail\Borrower\Reviewable;
use App\Models\Community;
use App\Models\User;
use Tests\TestCase;

class ReviewableTest extends TestCase
{
    public function testEmailContentWithZeroCommunity()
    {
        $user = factory(User::class)->create(["name" => "Test Name"]);

        $mail = new Reviewable($user, null);

        $mail_content = preg_replace("/\s+/", "", $mail->render());

        // Search for user name.
        $this->assertStringContainsString(
            "Un&bull;enouveau&bull;llemembre,TestName,acomplétésonprofild'emprunteur" .
                "danslescommunautéssuivantes&nbsp;:",
            $mail_content
        );

        // Search for empty list.
        $this->assertStringContainsString("<ul></ul>", $mail_content);
    }

    public function testEmailContentWithOneCommunity()
    {
        $user = factory(User::class)->create(["name" => "Test Name"]);

        $community = factory(Community::class)->create([
            "name" => "Community Name",
        ]);

        $mail = new Reviewable($user, $community);

        $mail_content = preg_replace("/\s+/", "", $mail->render());

        // Search for community name in title.
        $this->assertStringContainsString(
            "Profild&#039;emprunteurcomplétédansCommunityName",
            $mail_content
        );

        // Search for list.
        $this->assertStringContainsString(
            "<ul><li>CommunityName</li></ul>",
            $mail_content
        );
    }

    public function testEmailContentWithManyCommunities()
    {
        $user = factory(User::class)->create(["name" => "Test Name"]);

        $communities = [
            factory(Community::class)->create(["name" => "First Community"]),
            factory(Community::class)->create(["name" => "Second Community"]),
        ];

        $mail = new Reviewable($user, $communities);

        $mail_content = preg_replace("/\s+/", "", $mail->render());

        // Search for community name in title.
        $this->assertStringContainsString(
            "Profild&#039;emprunteurcomplétédans2communautés",
            $mail_content
        );

        // Search for list.
        $this->assertStringContainsString(
            "<ul><li>FirstCommunity</li><li>SecondCommunity</li></ul>",
            $mail_content
        );
    }
}
