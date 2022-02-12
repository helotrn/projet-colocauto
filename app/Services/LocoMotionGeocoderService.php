<?php

namespace App\Services;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use DB;

/** LocoMotion Geocoder
 *
 * This custom-made geocoder is built on top of 'geocoder-php'.
 *
 * Useful documentation:
 * - https://geocoder-php.org/docs/
 * - https://github.com/geocoder-php/Geocoder/blob/master/src/Common/Model/AddressCollection.php
 * - https://github.com/geocoder-php/Geocoder/blob/master/src/Common/Model/Address.php
 */

class LocoMotionGeocoderService
{
    public function __construct()
    {
        // Nothing yet
    }

    /**
     * RETURN A FULL ADDRESS OBJECT FROM GOOGLE
     * input @text
     */
    public static function geocode(string $full_text_address)
    {
        $httpClient = new \Http\Adapter\Guzzle7\Client();
        $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps(
            $httpClient,
            null,
            env("GOOGLE_API_KEY")
        );
        $geocoder = new \Geocoder\StatefulGeocoder($provider);

        $result = $geocoder->geocodeQuery(
            GeocodeQuery::create($full_text_address)
        );
        var_dump($result);
        return $result;
    }

    public static function findCommunityFromCoordinates(
        float $latitude,
        float $longitude
    ) {
        $rawQuery =
            "select * from(SELECT public.ST_Contains(area::geometry,ST_SetSRID('POINT(" .
            $longitude .
            " " .
            $latitude .
            ")'::geometry,4326)) in_area, name from communities) table_results where in_area is TRUE LIMIT 1";

        return $results = DB::select(DB::raw($rawQuery));
    }
}
