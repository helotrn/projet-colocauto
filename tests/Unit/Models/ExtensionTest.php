<?php

namespace Tests\Unit\Models;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Owner;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->user->communities()->save(factory(Community::class)->make());
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $this->loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);
        $this->loan = factory(Loan::class)
            ->states("withCompletedIntention")
            ->create([
                "loanable_id" => $this->loanable->id,
                "borrower_id" => $borrower->id,
                "duration_in_minutes" => 20,
            ]);
    }

    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCompleted());

        $extension->complete();

        $this->assertTrue($extension->isCompleted());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("completed", $extension->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCompleted());

        $extension->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($extension->isCompleted());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("completed", $extension->status);
        $this->assertEquals("2022-04-16 12:34:56", $extension->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "completed",
        ]);
        $loan->extensions()->save($extension);

        $this->assertTrue($extension->isCompleted());
    }

    public function testReject_Now()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCanceled());

        $extension->reject();

        $this->assertTrue($extension->isRejected());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("rejected", $extension->status);
    }

    public function testReject_At()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCanceled());

        $extension->reject(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($extension->isRejected());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("rejected", $extension->status);
        $this->assertEquals("2022-04-16 12:34:56", $extension->executed_at);
    }

    public function testIsRejected()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "rejected",
        ]);
        $loan->extensions()->save($extension);

        $this->assertTrue($extension->isRejected());
    }

    public function testCancel_Now()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCanceled());

        $extension->cancel();

        $this->assertTrue($extension->isCanceled());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("canceled", $extension->status);
    }

    public function testCancel_At()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "in_process",
        ]);
        $loan->extensions()->save($extension);

        $this->assertFalse($extension->isCanceled());

        $extension->cancel(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($extension->isCanceled());

        $this->assertNotNull($extension->executed_at);
        $this->assertEquals("canceled", $extension->status);
        $this->assertEquals("2022-04-16 12:34:56", $extension->executed_at);
    }

    public function testIsCanceled()
    {
        $loan = factory(Loan::class)->create();
        $extension = factory(Extension::class)->make([
            "status" => "canceled",
        ]);
        $loan->extensions()->save($extension);

        $this->assertTrue($extension->isCanceled());
    }

    public function testExtensionMakeLoanableUnavailable()
    {
        $departure = $this->loan->departure_at;
        $anHourLater = (new \Carbon\Carbon($this->loan->departure_at))->add(
            1,
            "hour"
        );
        $threeHoursLater = (new \Carbon\Carbon($this->loan->departure_at))->add(
            3,
            "hour"
        );

        $this->assertFalse($this->loanable->isAvailable($departure, 10));
        $this->assertTrue(
            $this->loanable->isAvailable($departure, 10, [$this->loan->id])
        );

        $this->assertTrue($this->loanable->isAvailable($anHourLater, 10));

        $extension = new Extension();
        $extension->fill([
            "new_duration" => 170,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "completed",
        ]);
        $extension->loan_id = $this->loan->id;
        $extension->save();

        $this->assertFalse($this->loanable->isAvailable($anHourLater, 10));
        $this->assertTrue($this->loanable->isAvailable($threeHoursLater, 10));
    }

    public function testCancelExtension()
    {
        // create a fake extension
        $extension = factory(Extension::class)->create([
            "loan_id" => $this->loan->id,
            "executed_at" => null,
            "status" => "in_process",
        ]);

        // verify if the change of status is successful
        $response = $this->json(
            "PUT",
            "/api/v1/loans/{$this->loan->id}/actions/{$extension->id}/cancel",
            [
                "type" => $extension->type,
            ]
        );
        $response->assertStatus(200);

        // verify if the values are correct
        $response = $this->json(
            "GET",
            "/api/v1/loans/{$this->loan->id}/actions/{$extension->id}"
        );
        $json = $response->json();

        $this->assertEquals($extension->id, array_get($json, "id"));
        $this->assertEquals("canceled", array_get($json, "status"));
    }

    public function testRejectExtension()
    {
        // create a fake extension
        $extension = factory(Extension::class)->create([
            "loan_id" => $this->loan->id,
            "executed_at" => null,
            "status" => "in_process",
        ]);

        // verify if the change of status is successful
        $response = $this->json(
            "PUT",
            "/api/v1/loans/{$this->loan->id}/actions/{$extension->id}/reject",
            [
                "type" => $extension->type,
            ]
        );
        $response->assertStatus(200);

        // verify if the values are correct
        $response = $this->json(
            "GET",
            "/api/v1/loans/{$this->loan->id}/actions/{$extension->id}"
        );
        $json = $response->json();

        $this->assertEquals($extension->id, array_get($json, "id"));
        $this->assertEquals("rejected", array_get($json, "status"));
    }
}
