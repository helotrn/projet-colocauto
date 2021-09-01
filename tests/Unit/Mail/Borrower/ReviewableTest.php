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

        $mail_content = preg_replace("/\s+/", " ", $mail->render());

        // Search for user name.
        $this->assertStringContainsString(
            "Un-e nouveau-lle membre, Test Name, a complété son profil d'emprunteur dans " .
                "les communautés suivantes&nbsp;:",
            $mail_content
        );

        // Search for empty list.
        $this->assertStringContainsString("<ul> </ul>", $mail_content);
    }

    public function testEmailContentWithOneCommunity()
    {
        $user = factory(User::class)->create(["name" => "Test Name"]);

        $community = factory(Community::class)->create([
            "name" => "Community Name",
        ]);

        $mail = new Reviewable($user, $community);

        $mail_content = preg_replace("/\s+/", " ", $mail->render());

        // Search for community name in title.
        $this->assertStringContainsString(
            "Profil d&#039;emprunteur complété dans Community Name",
            $mail_content
        );

        $style =
            'style=" text-align: left; font-weight: 390; ' .
            'font-size: 17px; line-height: 24px; color: #343a40; "';
        // Search for list.
        $this->assertStringContainsString(
            "<ul> <li $style > Community Name </li> </ul>",
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

        $mail_content = preg_replace("/\s+/", " ", $mail->render());

        // Search for community name in title.
        $this->assertStringContainsString(
            "Profil d&#039;emprunteur complété dans 2 communautés",
            $mail_content
        );

        $style =
            'style=" text-align: left; font-weight: 390; ' .
            'font-size: 17px; line-height: 24px; color: #343a40; "';
        // Search for list.
        $this->assertStringContainsString(
            "<ul> <li $style > First Community </li> <li $style > Second Community </li> </ul>",
            $mail_content
        );
    }
}
