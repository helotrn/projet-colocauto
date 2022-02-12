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
        // TODO
        // $this->assertNotEmpty($response);
    }

    /**
     * Test we actually get a Community in return for a lat/lng we support
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
        // TODO
        // $this->assertInstanceOf(App\Models\Community, $result);
    }
}
