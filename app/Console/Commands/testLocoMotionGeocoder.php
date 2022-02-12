<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LocoMotionGeocoderService as LocoMotionGeocoder;
use Log;

class testLocoMotionGeocoder extends Command
{
    protected $signature = "test:geocoder";
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $response = LocoMotionGeocoder::geocode(
            "962 Avenue Laurier Est, Montreal"
        );
        $locations = $response->first()->getCoordinates();
        Log::info(var_dump($locations));
        return 0;
    }
}
