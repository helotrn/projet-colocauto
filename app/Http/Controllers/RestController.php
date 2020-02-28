<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Molotov\Controllers\RestController as MolotovRestController;

class RestController extends MolotovRestController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
