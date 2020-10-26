<?php

namespace Tests\Unit\Providers;

use App\Events\BorrowerCompletedEvent;
use App\Listeners\SendBorrowerCompletedEmails;
use Mockery;
use App\Models\User;
use Tests\TestCase;

class EventServiceProviderTest extends TestCase
{
    public function testBorrowerCompletedEventTriggersSendBorrowerCompletedEmails() {
        $user = factory(User::class)->create();

        $listener = Mockery::spy(SendBorrowerCompletedEmails::class);
        app()->instance(SendBorrowerCompletedEmails::class, $listener);

                             // Trigger event
        event(new BorrowerCompletedEvent($user));

        $listener->shouldHaveReceived('handle')
            ->with(Mockery::on(function ($event) use ($user) {
                return $event->user && $event->user->id == $user->id;
            }))
            ->once();
    }
}
