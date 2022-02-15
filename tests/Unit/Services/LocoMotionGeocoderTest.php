<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\LocoMotionGeocoderService as LocoMotionGeocoder;
use Geocoder\Model\Address;

class LocoMotionGeocoderTest extends TestCase
{
    /**
     * Test the structure of the return format for a identifiable address
     */
    public function testLocoMotionGeocoderAutoCompleteIdentifiableAddress()
    {
        $response = LocoMotionGeocoder::geocode(
            "6450, Ave Christophe-Colomb, Montréal, QC, H2S 2G7"
        );
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(Address::class, $response);
    }

    /**
     * Test the structure of the return format for a unknown address
     */
    public function testLocoMotionGeocoderAutoCompleteUnknowAddress()
    {
        $response = LocoMotionGeocoder::geocode("weird address");
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(Address::class, $response);
    }

    /**
     * Test we actually get a Community::class in return for a lat/lng we support
     * *
     * TODO: Since it interacts with the DB, we can't run it in a unit testing
     */
    public function testLocoMotionGeocoderCommunityFinder()
    {
        // Solon Office
        // 6450 Ave Christophe-Colomb, Montreal, Quebec H2S 2G7
        $coordinates = ["latitude" => "45.537479", "longitude" => "-73.601448"];
        // $response = LocoMotionGeocoder::findCommunityFromCoordinates(
        //     $coordinates["latitude"],
        //     $coordinates["longitude"]
        // );
        // $this->assertInstanceOf(App\Models\Community::class, $result);
    }
}
