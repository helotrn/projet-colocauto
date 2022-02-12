<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\LocoMotionGeocoderService as LocoMotionGeocoder;

class LocoMotionGeocoderTest extends TestCase
{
    /**
     * Test the structure of the return format for a identifiable address
     */
    public function testLocoMotionGeocoderAutoCompleteIdentifiableAddress()
    {
        $response = LocoMotionGeocoder::geocode(
            "962 Avenue Laurier Est, Montreal"
        );
        $this->assertNotEmpty($response);
    }

    /**
     * Test the structure of the return format for a unknown address
     */
    public function testLocoMotionGeocoderAutoCompleteUnknowAddress()
    {
        $response = LocoMotionGeocoder::geocode("weird address");
        $this->assertEmpty($response);
    }
}
