<?php

namespace Tests\Unit\Models;

use App\Events\BorrowerCompletedEvent;
use App\Models\Borrower;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BorrowerTest extends TestCase
{
    public function testEmitsBorrowerCompletedEvent()
    {
        Event::fake([BorrowerCompletedEvent::class]);

        $user = factory(User::class)->create();

        $borrower = factory(Borrower::class)->create([
            "user_id" => $user->id,
            "drivers_license_number" => "C1234-123456-12",
            "has_been_sued_last_ten_years" => false,
        ]);

        $gaa = factory(File::class)->create([
            "path" => "test/gaa.txt",
            "filename" => "gaa.txt",
            "original_filename" => "gaa.txt",
            "filesize" => 1234,
            "fileable_type" => "App\Models\Borrower",
            "fileable_id" => $borrower->id,
            "field" => "gaa",
        ]);

        $saaq = factory(File::class)->create([
            "path" => "test/saaq.txt",
            "filename" => "saaq.txt",
            "original_filename" => "saaq.txt",
            "filesize" => 1234,
            "fileable_type" => "App\Models\Borrower",
            "fileable_id" => $borrower->id,
            "field" => "saaq",
        ]);

        $borrower = $borrower->fresh();
        $borrower->save();

        Event::assertDispatched(BorrowerCompletedEvent::class);
    }
}
