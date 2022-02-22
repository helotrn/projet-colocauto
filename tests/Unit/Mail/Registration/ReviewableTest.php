<?php

/**
 * tests\Unit\Mail\Registration\ReviewableTest
 *
 * Test the integrity of the notification send to administrators when a user completes its registration
 */

namespace Tests\Unit\Mail\Registration;

use App\Mail\Registration\Reviewable as RegistrationReviewable;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewableTest extends TestCase
{
    public function testEmailContent()
    {
        $user = factory(User::class)->create([
            "name" => "John",
            "last_name" => "Doe",
        ]);

        $community = factory(Community::class)->create([
            "name" => "Community_Name",
        ]);

        $mail = new RegistrationReviewable($user, $community);

        $mail_content = preg_replace("/\s+/", " ", $mail->render());

        // Search for the community's name.
        $this->assertStringContainsString("Community_Name", $mail_content);

        // Search for the member's name.
        $this->assertStringContainsString("John Doe", $mail_content);
    }
}
