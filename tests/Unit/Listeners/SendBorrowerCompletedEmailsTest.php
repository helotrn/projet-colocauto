<?php

namespace Tests\Unit\Listeners;

use App\Events\BorrowerCompletedEvent;
use App\Listeners\SendBorrowerCompletedEmails;
use App\Mail\Borrower\Completed as BorrowerCompleted;
use App\Mail\Borrower\Reviewable as BorrowerReviewable;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendBorrowerCompletedEmailsTest extends TestCase
{
    /*
  This tests that the email is indeed sent to the borrower.
  It also indirectly tests the case of an app instance without admin.
*/
    public function testSendsEmailToBorrower()
    {
        Mail::fake();

        $user = factory(User::class)->create();
        $community = factory(Community::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);

        $event = new BorrowerCompletedEvent($user);

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerCompletedEmails::class);
        $listener->handle($event);

        // Mail to borrower.
        Mail::assertQueued(BorrowerCompleted::class, function ($mail) use (
            $user
        ) {
            return $mail->hasTo($user->email);
        });
    }

    public function testSendsEmailToAdminsOfSameLevel()
    {
        Mail::fake();

        $user = factory(User::class)->create();

        $community = factory(Community::class)->create();
        $parent_community = factory(Community::class)->create([
            "parent_id" => $community->id,
        ]);

        $global_admin = factory(User::class)->create(["role" => "admin"]);

        $community_admin = factory(User::class)->create();
        $parent_community_admin = factory(User::class)->create();

        $community->users()->attach($community_admin, ["role" => "admin"]);

        $parent_community
            ->users()
            ->attach($parent_community_admin, ["role" => "admin"]);

        // Attach to community
        $community->users()->attach($user);

        $event = new BorrowerCompletedEvent($user);

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerCompletedEmails::class);
        $listener->handle($event);

        // Mail to global admin
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $global_admin
        ) {
            return $mail->hasTo($global_admin->email);
        });

        // Check that community admin will get email.
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $community_admin
        ) {
            return $mail->hasTo($community_admin->email);
        });

        // Check that parent community admin will not get email.
        Mail::assertNotQueued(BorrowerReviewable::class, function ($mail) use (
            $parent_community_admin
        ) {
            return $mail->hasTo($parent_community_admin->email);
        });
    }

    /*
  Tests that the email is sent to admins when they are registered at the same
  level as the user.
*/
    public function testSendsEmailToAdminsOfDifferentLevels()
    {
        Mail::fake();

        $user = factory(User::class)->create();

        $community = factory(Community::class)->create();
        $parent_community = factory(Community::class)->create([
            "parent_id" => $community->id,
        ]);

        $global_admin = factory(User::class)->create(["role" => "admin"]);

        $community_admin = factory(User::class)->create();
        $parent_community_admin = factory(User::class)->create();

        $community->users()->attach($community_admin, ["role" => "admin"]);

        $parent_community
            ->users()
            ->attach($parent_community_admin, ["role" => "admin"]);

        // Attach to parent community
        $parent_community->users()->attach($user);

        $event = new BorrowerCompletedEvent($user);

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerCompletedEmails::class);
        $listener->handle($event);

        // Mail to global admin
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $global_admin
        ) {
            return $mail->hasTo($global_admin->email);
        });

        // Check that community admin will not get email.
        Mail::assertNotQueued(BorrowerReviewable::class, function ($mail) use (
            $community_admin
        ) {
            return $mail->hasTo($community_admin->email);
        });

        // Check that parent community admin will get email.
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $parent_community_admin
        ) {
            return $mail->hasTo($parent_community_admin->email);
        });
    }

    /*
  Tests that the email is sent to admins when they are registered at the same
  level as the user.
*/
    public function testSendsEmailToAdminsMultipleCommunities()
    {
        Mail::fake();

        $user = factory(User::class)->create();

        $community = factory(Community::class)->create();
        $parent_community = factory(Community::class)->create([
            "parent_id" => $community->id,
        ]);

        $global_admin = factory(User::class)->create(["role" => "admin"]);

        $community_admin = factory(User::class)->create();
        $parent_community_admin = factory(User::class)->create();

        $community->users()->attach($community_admin, ["role" => "admin"]);

        $parent_community
            ->users()
            ->attach($parent_community_admin, ["role" => "admin"]);

        // Attach to both community
        $community->users()->attach($user);
        $parent_community->users()->attach($user);

        $event = new BorrowerCompletedEvent($user);

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerCompletedEmails::class);
        $listener->handle($event);

        // Mail to global admin
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $global_admin
        ) {
            return $mail->hasTo($global_admin->email);
        });

        // Check that one of community or parent community
        // admin will get email.
        Mail::assertQueued(BorrowerReviewable::class, function ($mail) use (
            $community_admin,
            $parent_community_admin
        ) {
            return $mail->hasTo($community_admin->email) ||
                $mail->hasTo($parent_community_admin->email);
        });
    }
}
