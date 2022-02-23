<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    public function testAmountFormatting()
    {
        $this->assertEquals(Invoice::formatAmountForDisplay(10.124), "10,12");
        $this->assertEquals(Invoice::formatAmountForDisplay(10.125), "10,13");
        $this->assertEquals(
            Invoice::formatAmountForDisplay(10000.126),
            "10 000,13"
        );
    }
}
