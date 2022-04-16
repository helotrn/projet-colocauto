<?php

/**
 * tests\Unit\Mail\Registration\ReviewableTest
 *
 * Test the integrity of the notification sent to administrators when a user completes its registration
 */

namespace Tests\Unit\Mail\Registration;
use App\Events\RegistrationSubmittedEvent;
use App\Listeners\SendRegistrationSubmittedEmails;
use App\Mail\Registration\Reviewable as RegistrationReviewable;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationReviewableTest extends TestCase
{
    public function testRegistrationAdminEmailContentIntegrity()
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

    public function testRegistrationAdminEmailDelivery()
    {
        // Prevent the event to send customer's email since this test is admin-only.
        $meta = [];
        $meta["sent_registration_submitted_email"] = true;
        $user = factory(User::class)->create([
            "name" => "John",
            "last_name" => "Doe",
            "meta" => $meta,
        ]);

        $community = factory(Community::class)->create([
            "name" => "Community_Name",
        ]);

        // Don't trigger event. Only test listener.
        $event = new RegistrationSubmittedEvent($user);
        // $mail = new RegistrationReviewable($user, $community);
        // $listener = app()->make(SendRegistrationSubmittedEmails::class);
        // $listener->handle($event);

        // // Mail to borrower.
        // Find inspiration from testSendsEmailToAdminsOfSameLevel for what's next
        // Mail::assertQueued(RegistrationReviewable::class, function ($mail) use (
        //     $user
        // ) {
        //     return $mail->hasTo($user->email);
        // });
    }
}
