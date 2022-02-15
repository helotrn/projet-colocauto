<?php

namespace App\Services;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Model\Address;
use App\Models\Community;
use DB;

/** LocoMotion Geocoder
 *
 * This custom-made geocoder is built on top of 'geocoder-php'.
 *
 * Useful documentation:
 * - https://geocoder-php.org/docs/
 * - https://github.com/geocoder-php/Geocoder/blob/master/src/Common/Model/AddressCollection.php
 * - https://github.com/geocoder-php/Geocoder/blob/master/src/Common/Model/Address.php (Address)
 */

class LocoMotionGeocoderService
{
    public function __construct()
    {
        // Nothing yet
    }

    /**
     * RETURN A GEOCODER-PHP ADDRESS OBJECT FROM A TEXTUAL ADDRESS
     * input @string
     * return @Address
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
        return $result->first();
    }

    /**
     * Convert address object to a full textual address
     * *
     * @input Address
     * @return String
     * Ex: '9155 Rue St-Hubert, Montréal, H2M 1Y8, Canada'
     */
    public static function formatAddressToText(Address $address)
    {
        $full_address =
            $address->getStreetNumber() .
            " " .
            $address->getStreetName() .
            ", " .
            $address->getLocality() .
            "," .
            $address->getPostalCode() .
            "," .
            $address->getCountry();
        return $full_address;
    }

    /**
     * Find Community From Coordinates
     * *
     * @input (float:latitude, float:longitude)
     * @return Community
     */
    public static function findCommunityFromCoordinates(
        float $latitude,
        float $longitude
    ) {
        $rawQuery =
            "select * from(SELECT public.ST_Contains(area::geometry,ST_SetSRID('POINT(" .
            $longitude .
            " " .
            $latitude .
            ")'::geometry,4326)) in_area, communities.id, communities.name from communities WHERE communities.type = 'borough') table_results where in_area is TRUE LIMIT 1";

        $results = DB::select(DB::raw($rawQuery));
        if (count($results) > 0) {
            return Community::find($results[0]->id);
        } else {
            return null;
        }
    }
}
