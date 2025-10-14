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

class ReviewableTest extends TestCase
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

    public function testRegistrationAdminsEmailDelivery()
    {
        $this->markTestSkipped('Mail::assertQueued is not working ?');

        Mail::fake();

        // Prevent the event to send customer's email since this test is admin-only.
        $meta = [];
        $meta["sent_registration_submitted_email"] = true;

        // Fake User
        $user = factory(User::class)->create([
            "name" => "John",
            "last_name" => "Doe",
            "meta" => $meta,
        ]);
        $community = factory(Community::class)->create([
            "name" => "Community_Name",
        ]);
        $community->users()->attach($user);

        // Fake global and local admins
        $global_admin = factory(User::class)->create(["role" => "admin"]);
        $community_admin = factory(User::class)->create();
        $community->users()->attach($community_admin, ["role" => "admin"]);

        // This doesn't trigger Event but only the Listener.
        $event = new RegistrationSubmittedEvent($user);
        $listener = app()->make(SendRegistrationSubmittedEmails::class);
        $listener->handle($event);

        // Check mail delivery for global admin
        Mail::assertQueued(RegistrationReviewable::class, function ($mail) use (
            $global_admin
        ) {
            return $mail->hasTo($global_admin->email);
        });

        // Check mail delivery for local admin
        Mail::assertQueued(RegistrationReviewable::class, function ($mail) use (
            $community_admin
        ) {
            return $mail->hasTo($community_admin->email);
        });
    }
}
